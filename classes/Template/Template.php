<?php

namespace Rhino\Codegen\Template;

use Rhino\Codegen\Codegen;
use Rhino\Codegen\Hook;
use Rhino\Codegen\MergeClass;
use Rhino\Codegen\TempFile;

abstract class Template
{
    protected $codegen;
    protected $namespaces = [
        'model-implemented' => 'Model',
        'model-generated' => 'Model\Generated',
        'model-serializer' => 'Model\Serializer',
        'controller-implemented' => 'Controller',
        'controller-generated' => 'Controller\Generated',
        'controller-api-implemented' => 'Controller\Api',
        'controller-api-generated' => 'Controller\Api\Generated',
        'controller-admin-implemented' => 'Controller\Admin',
        'controller-admin-generated' => 'Controller\Admin\Generated',
        'data-table-admin-generated' => 'Controller\Admin\DataTable',
        'test-model' => 'Test\Model',
    ];
    protected $templateOverrides = [];
    protected $paths = [];
    protected $hooks = [
        'gen:pre' => [],
        'gen:post' => [],
    ];

    abstract public function generate();

    public function getCodegen(): \Rhino\Codegen\Codegen
    {
        return $this->codegen;
    }

    public function setCodegen(\Rhino\Codegen\Codegen $value): self
    {
        $this->codegen = $value;
        return $this;
    }

    public function setPath(string $template, string $path): self
    {
        $this->paths[$template] = $path;
        return $this;
    }

    public function getPath(string $template): ?string
    {
        return $this->paths[$template];
    }

    public function getNamespace(string $type): string
    {
        if (!isset($this->namespaces[$type])) {
            throw new \Exception('Could not find namespace for type ' . $type);
        }
        return rtrim($this->codegen->getNamespace() . '\\' . $this->namespaces[$type], '\\');
    }

    public function setNamespace(string $type, string $namespace): self
    {
        $this->namespaces[$type] = $namespace;
        return $this;
    }

    public function setDefaultNamespace(string $type, string $namespace): self
    {
        if (!isset($this->namespaces[$type])) {
            $this->namespaces[$type] = $namespace;
        }
        return $this;
    }

    public function iterateRoutes()
    {
        return [];
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function setNamespaces(array $value): self
    {
        $this->namespaces = $value;
        return $this;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function setPaths(array $value): self
    {
        $this->paths = $value;
        return $this;
    }

    public function hook(string $hookName, array $parameters): array
    {
        $parameters = $this->codegen->hook($hookName, $parameters);
        if (isset($this->hooks[$hookName])) {
            foreach ($this->hooks[$hookName] as $hook) {
                $parameters = $hook->process(...$parameters);
            }
        }
        return $parameters;
    }

    public function addHook(Hook\Hook $hook): self
    {
        $this->hooks[$hook->getHook()][] = $hook;
        return $this;
    }

    public function addHookCallback(string $hookName, callable $callback): self
    {
        $this->hooks[$hookName][] = new Hook\Callback($hookName, $callback);
        return $this;
    }

    protected function bufferTemplate(string $template, array $data = [])
    {
        $templateFile = $this->getTemplateFile($template);
        return $this->bufferTemplateFile($templateFile, $data);
    }

    protected function renderTemplate(string $template, string $outputFile, array $data = [], bool $overwrite = true)
    {
        $outputFile = $this->getFilePath($template, $outputFile, $data);
        if (file_exists($outputFile)) {
            $outputFile = realpath($outputFile);
        }

        $templateFile = $this->getTemplateFile($template);
        return $this->renderTemplateFile($templateFile, $outputFile, $data, $overwrite);
    }

    protected function bufferTemplateFile(string $templateFile, array $data = [])
    {
        if (preg_match('/\.php$/', $templateFile)) {
            $codegen = $this->getCodegen();
            extract(get_object_vars($this), EXTR_SKIP);
            extract($data, EXTR_SKIP);
            ob_start();
            require $templateFile;
            return ob_get_clean();
        }
        return file_get_contents($templateFile);
    }

    protected function queueTemplateFile(string $templateFile, string $outputPath, array $data = [], bool $overwrite = true)
    {
        $dataFile = \Rhino\Codegen\ROOT . '/temp/' . uniqid('data-', true) . '.ser';
        file_put_contents($dataFile, serialize($data));
        $command = $this->getCodegen()->getCliArguments('render:template-file', [
            '--template' => static::class,
            '--template-file' => $templateFile,
            '--output-file' => $outputPath,
            '--data-file' => $dataFile,
            // '--overwrite' => $overwrite,
        ]);
        $this->getCodegen()->queue($command);
    }

    public function renderTemplateFile(string $templateFile, string $outputPath, array $data = [], bool $overwrite = true)
    {
        if (file_exists($outputPath)) {
            $outputPath = realpath($outputPath);
            if (!$overwrite) {
                $this->codegen->debug('Skipped ' . $outputPath);
                return null;
            }
        }
        $this->codegen->log('Rendering', $outputPath, $templateFile);
        $output = $this->bufferTemplateFile($templateFile, $data);
        $generatedHash = md5($output);
        $fileHash = md5_file($outputPath);
        if ($fileHash != md5('')) {
            $manifestHashes = $this->codegen->manifest->getHashes($outputPath);
            if ($generatedHash === ($manifestHashes['generatedHash'] ?? null) && ($manifestHashes['generatedHash'] ?? null) && !$this->codegen->isForce()) {
                $this->codegen->log('No changes detected.', $generatedHash, $manifestHashes['generatedHash'] ?? null);
                return;
            } else {
                $this->codegen->debug('Changes detected.', $generatedHash, $manifestHashes['generatedHash'] ?? null);
            }
            if ($fileHash !== ($manifestHashes['formattedHash'] ?? null) && ($manifestHashes['formattedHash'] ?? null) && !$this->codegen->isForce()) {
                $this->codegen->log('Changes detected in generated file, not overwriting.', $fileHash, $manifestHashes['formattedHash'] ?? null);
                return;
            }
        }

        $this->codegen->log('Formatting generated code.');
        $tempFile = TempFile::createUnique(pathinfo($outputPath, PATHINFO_EXTENSION));
        $tempFile->putContents($output);
        $this->hook('format', [$tempFile]);
        $formattedHash = md5_file($tempFile->getPath());

        $this->codegen->manifest->addFile($outputPath, [
            'generatedHash' => $generatedHash,
            'formattedHash' => $formattedHash,
        ]);

        $this->codegen->log('Writing output file.');
        $directory = dirname($outputPath);
        $this->codegen->createDirectory($directory);
        $tempFile->copyTo($outputPath);
    }

    // protected function mergeTemplateFile(string $templateFile, string $outputPath, array $data = []): ?OutputFile
    // {
    //     if (!file_exists($outputPath)) {
    //         return $this->renderTemplateFile($templateFile, $outputPath, $data);
    //     }
    //     $outputFile = new OutputFile($outputPath);
    //     $output = $this->bufferTemplateFile($templateFile, $data);
    //     $output = MergeClass::mergeStrings($this->codegen, $output, $outputFile->getContents());
    //     // [$output, $outputFile] = $this->hook('gen:post', [$output, $outputFile]);
    //     if ($this->codegen->writeFile($outputPath, $output)) {
    //         $this->hook('gen:write', [$outputFile]);
    //         $this->codegen->manifest->addFile($outputFile->getPath());
    //     }
    //     return $outputFile;
    // }

    protected function getTemplateFile(string $name): string
    {
        if (isset($this->templateOverrides[$name])) {
            return $this->templateOverrides[$name];
        }
        $possibilities = [
            __DIR__ . '/../../templates/' . $name . '.php',
            __DIR__ . '/../../templates/' . $name,
            $name,
        ];
        foreach ($possibilities as $possibility) {
            if (is_file($possibility)) {
                return realpath($possibility);
            }
        }
        throw new \Exception('Could not find template file: ' . $name . ' tried ' . implode(', ', $possibilities));
    }

    public function setTemplateFile(string $name, string $path): self
    {
        if (!is_file($path)) {
            throw new \Exception('Template override path is not a valid file: ' . $path);
        }
        $this->templateOverrides[$name] = $path;
        return $this;
    }

    protected function copy(string $from, string $to)
    {
        $from = __DIR__ . '/../../templates/' . $from;
        $to = $this->getFilePath(null, $to);
        $this->codegen->createDirectory($to);
        foreach (glob($from) as $fromFile) {
            $toFile = $to . '/' . basename($fromFile);
            $this->codegen->writeFile($toFile, file_get_contents($fromFile));
        }
    }

    protected function createFiles(array $files)
    {
        foreach ($files as $file) {
            $directory = dirname($file);
            $this->codegen->createDirectory();
            if (!file_exists($file)) {
                $this->codegen->log('Creating file ' . $file);
                if (!$this->codegen->isDryRun()) {
                    file_put_contents($file, '');
                }
            }
        }
    }

    protected function getFilePath(?string $template, string $file, array $data = []): string
    {
        if (!isset($this->paths[$template])) {
            $path = $this->codegen->getPath() . '/' . $file;
        } else {
            $path = $this->codegen->getPath() . '/' . $this->paths[$template];
        }
        $path = preg_replace_callback('/{{(?<expression>.*?)}}/', function ($matches) use ($data) {
            extract($data);
            return eval('return ' . $matches['expression'] . ';');
        }, $path);
        return $path;
    }
}
