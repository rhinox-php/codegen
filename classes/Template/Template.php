<?php
namespace Rhino\Codegen\Template;
use Rhino\Codegen\Codegen;

abstract class Template {

    protected $codegen;
    protected $namespaces = [
        'model-implemented' => 'Model',
        'model-generated' => 'Model\Generated',
        'model-serializer' => 'Model\Serializer',
        'controller-implemented' => 'Controller',
        'controller-generated' => 'Controller\Generated',
        'controller-api-implemented' => 'Controller\Api',
        'controller-api-generated' => 'Controller\Api\Generated',
        'test-model' => 'Test\Model',
    ];
    protected $paths = [];
    protected $merge = false;
    protected $hooks = [
        'gen:pre' => [],
        'gen:post' => [],
    ];

    public abstract function generate();

    protected function bufferTemplate(string $template, array $data = []) {
        $templateFile = $this->getTemplateFile($template);
        return $this->bufferTemplateFile($templateFile, $data);
    }

    protected function renderTemplate(string $template, string $outputFile, array $data = [], bool $overwrite = true) {
        $outputFile = $this->getFilePath($template, $outputFile, $data);
        if (file_exists($outputFile)) {
            $outputFile = realpath($outputFile);
        }

        $templateFile = $this->getTemplateFile($template);
        return $this->renderTemplateFile($templateFile, $outputFile, $data, $overwrite);
    }

    protected function bufferTemplateFile(string $templateFile, array $data = []) {
        $codegen = $this->getCodegen();
        extract(get_object_vars($this), EXTR_SKIP);
        extract($data, EXTR_SKIP);
        ob_start();
        require $templateFile;
        return ob_get_clean();
    }

    protected function renderTemplateFile(string $templateFile, string $outputFile, array $data = [], bool $overwrite = true) {
        if (file_exists($outputFile)) {
            $outputFile = realpath($outputFile);
            if (!$overwrite) {
                $this->codegen->debug('Skipped ' . $outputFile);
                return;
            }
        }
        $output = $this->bufferTemplateFile($templateFile, $data);
        $directory = dirname($outputFile);
        $this->codegen->createDirectory($directory);
        if ($this->getMerge()) {
            if (is_file($outputFile)) {
                var_dump($outputFile);
            }

            $merge = new \Rhino\Codegen\MergeClass($this->codegen);
            if (!is_file($outputFile)) {
                $this->codegen->writeFile($outputFile, $output);
                return;
            }
            $merge->setClassSourceFrom($output);
            $merge->setClassSourceInto(file_get_contents($outputFile));
            $merge->parse();
            $this->codegen->writeFile($outputFile, $merge->getOutput());
            return;
        }
        [$output, $outputFile] = $this->hook('gen:post', [$output, $outputFile]);
        $this->codegen->writeFile($outputFile, $output);
    }

    protected function getTemplateFile($name) {
        $standardFile = __DIR__ . '/../../templates/' . $name . '.php';
        if (!is_file($standardFile)) {
            if (!is_file($name)) {
                throw new \Exception('Could not find template file: ' . $name . ' tried ' . $standardFile . ' and ' . $name);
            }
            return realpath($name);
        }
        return realpath($standardFile);
    }

    protected function createFiles(array $files) {
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

    protected function hook(string $hookName, array $parameters): array {
        foreach ($this->hooks[$hookName] as $hook) {
            $parameters = $hook(...$parameters);
        }
        return $parameters;
    }

    public function getCodegen(): \Rhino\Codegen\Codegen {
        return $this->codegen;
    }

    public function setCodegen(\Rhino\Codegen\Codegen $value): self {
        $this->codegen = $value;
        return $this;
    }

    private function getFilePath(string $template, string $file, array $data): string {
        if (!isset($this->paths[$template])) {
            $path = $this->codegen->getPath() . '/' . $file;
        } else {
            $path = $this->codegen->getPath() . '/' . $this->paths[$template];
        }
        $path = preg_replace_callback('/{{(?<expression>.*?)}}/', function($matches) use($data) {
            extract($data);
            return eval('return ' . $matches['expression'] . ';');
        }, $path);
        return $path;
    }

    public function setPath(string $template, string $path): self {
        $this->paths[$template] = $path;
        return $this;
    }

    public function getNamespace(string $type): string {
        if (!isset($this->namespaces[$type])) {
            throw new \Exception('Could not find namespace for type ' . $type);
        }
        return rtrim($this->codegen->getNamespace() . '\\' . $this->namespaces[$type], '\\');
    }

    public function setNamespace(string $type, string $namespace): self {
        $this->namespaces[$type] = $namespace;
        return $this;
    }

    public function iterateRoutes() {
        return [];
    }

    public function getNamespaces(): array {
        return $this->namespaces;
    }

    public function setNamespaces(array $value): self {
        $this->namespaces = $value;
        return $this;
    }

    public function getPaths(): array {
        return $this->paths;
    }

    public function setPaths(array $value): self {
        $this->paths = $value;
        return $this;
    }

    public function getMerge(): bool {
        return $this->merge;
    }

    public function setMerge(bool $merge): self {
        $this->merge = $merge;
        return $this;
    }

    public function addHook(string $hookName, callable $callback): self {
        $this->hooks[$hookName][] = $callback;
        return $this;
    }
}
