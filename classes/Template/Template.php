<?php
namespace Rhino\Codegen\Template;
use Rhino\Codegen\Codegen;

abstract class Template {

    protected $codegen;
    protected $name;
    protected $templateOverrides = [];
    protected $path = null;
    protected $namespaces = [
        'model-implemented' => 'Model',
        'model-generated' => 'Model\Generated',
        'model-serializer' => 'Model\Serializer',
        'controller-implemented' => 'Controller',
        'controller-generated' => 'Controller\Generated',
        'controller-api-implemented' => 'Controller\Api',
        'controller-api-generated' => 'Controller\Api\Generated',
    ];

    public abstract function generate();

    protected function bufferTemplate($template, array $data = []) {
        $codegen = $this->getCodegen();
        $file = $this->getTemplateFile($template);
        extract(get_object_vars($this), EXTR_SKIP);
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean();
    }

    protected function renderTemplate($template, $outputFile, array $data = [], $overwrite = true) {
        $outputFile = $this->getFilePath($outputFile);
        if (file_exists($outputFile)) {
            $outputFile = realpath($outputFile);
            if (!$overwrite) {
                $this->getCodegen()->debug('Skipped ' . $outputFile);
                return;
            }
        } elseif (is_dir(dirname($outputFile))) {
            $outputFile = realpath(dirname($outputFile)) . DIRECTORY_SEPARATOR . basename($outputFile);
        }

        $templateFile = $this->getTemplateFile($template);
        $output = $this->bufferTemplate($template, $data);
        $directory = dirname($outputFile);
        $this->getCodegen()->createDirectory($directory);

        if ($overwrite && is_file($outputFile) && md5($output) == md5_file($outputFile)) {
            $this->getCodegen()->debug('No changes ' . $outputFile . ' from template ' . $templateFile);
            return;
        }

        $this->getCodegen()->writeFile($outputFile, $output);
    }

    protected function getTemplateFile($name) {
        $standardFile = __DIR__ . '/../../templates/' . $this->name . '/' . $name . '.php';
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
            $this->getCodegen()->createDirectory();
            if (!file_exists($file)) {
                $this->getCodegen()->log('Creating file ' . $file);
                if (!$this->getCodegen()->isDryRun()) {
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

    public function setTemplateOverride($name, $file) {
        if (!is_file($file)) {
            throw new \Exception('Could not find template override file: ' . $name . ' tried: ' . $file);
        }
        $this->templateOverrides[$name] = $file;
        return $this;
    }

    public function getFilePath($file) {
        return $this->getPath() . '/' . $file;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function setPath(string $path): self {
        $this->path = $path;
        return $this;
    }

    public function getNamespace(string $type): string {
        return $this->getCodegen()->getNamespace() . '\\' . $this->namespaces[$type];
    }

    public function setNamespace(string $namespace): self {
        $this->namespace = $namespace;
        return $this;
    }

    public function iterateRoutes() {
        return [];
    }
}
