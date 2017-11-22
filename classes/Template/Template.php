<?php
namespace Rhino\Codegen\Template;

use Rhino\Codegen\Codegen;
use Rhino\Codegen\Hook;

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
        'test-model' => 'Test\Model',
    ];
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
        $codegen = $this->getCodegen();
        extract(get_object_vars($this), EXTR_SKIP);
        extract($data, EXTR_SKIP);
        ob_start();
        require $templateFile;
        return ob_get_clean();
    }

    protected function renderTemplateFile(string $templateFile, string $outputPath, array $data = [], bool $overwrite = true): ?OutputFile
    {
        if (file_exists($outputPath)) {
            $outputPath = realpath($outputPath);
            if (!$overwrite) {
                $this->codegen->debug('Skipped ' . $outputPath);
                return null;
            }
        }
        $outputFile = new OutputFile($outputPath);
        $output = $this->bufferTemplateFile($templateFile, $data);
        $directory = dirname($outputPath);
        $this->codegen->createDirectory($directory);
        [$output, $outputFile] = $this->hook('gen:post', [$output, $outputFile]);
        $this->codegen->writeFile($outputPath, $output);
        $this->hook('gen:write', [$outputFile]);
        return $outputFile;
    }

    protected function getTemplateFile($name)
    {
        $standardFile = __DIR__ . '/../../templates/' . $name . '.php';
        if (!is_file($standardFile)) {
            if (!is_file($name)) {
                throw new \Exception('Could not find template file: ' . $name . ' tried ' . $standardFile . ' and ' . $name);
            }
            return realpath($name);
        }
        return realpath($standardFile);
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

    protected function getFilePath(string $template, string $file, array $data): string
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
