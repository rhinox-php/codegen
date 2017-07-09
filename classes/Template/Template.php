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

    public abstract function generate();

    protected function bufferTemplate(string $template, array $data = []) {
        $templateFile = $this->getTemplateFile($template);
        return $this->bufferTemplateFile($templateFile, $data);
    }

    protected function renderTemplate(string $template, string $outputFile, array $data = [], bool $overwrite = true) {
        $outputFile = $this->getFilePath($template, $outputFile);
        if (file_exists($outputFile)) {
            $outputFile = realpath($outputFile);
        } elseif (is_dir(dirname($outputFile))) {
            $outputFile = realpath(dirname($outputFile)) . DIRECTORY_SEPARATOR . basename($outputFile);
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

    public function getCodegen(): Codegen {
        return $this->codegen;
    }

    public function setCodegen(Codegen $codegen): Template {
        $this->codegen = $codegen;
        return $this;
    }

    public function getFilePath(string $template, string $file): string {
        if (!isset($this->paths[$template])) {
            return $this->codegen->getPath() . '/' . $file;
        }
        return $this->codegen->getPath() . '/' . $this->paths[$template] . '/' . basename($file);
    }

    public function setPath(string $template, string $path): self {
        $this->paths[$template] = $path;
        return $this;
    }

    public function getNamespace(string $type): string {
        if (!isset($this->namespaces[$type])) {
            throw new \Exception('Could not find namespace for type ' . $type);
        }
        return $this->codegen->getNamespace() . '\\' . $this->namespaces[$type];
    }

    public function setNamespace(string $type, string $namespace): self {
        $this->namespaces[$type] = $namespace;
        return $this;
    }

    public function iterateRoutes() {
        return [];
    }
}
