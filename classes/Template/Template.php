<?php
namespace Rhino\Codegen\Template;

abstract class Template {

    protected $name;
    protected $templateOverrides = [];

    public abstract function generate();
    
    protected function bufferTemplate($template, array $data = []) {
        $codegen = $this->codegen;
        $file = $this->getTemplateFile($template);
        extract(get_object_vars($this), EXTR_SKIP);
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean();
    }

    protected function renderTemplate($template, $outputFile, array $data = [], $overwrite = true) {
        if (!$overwrite && file_exists($outputFile)) {
            $this->debug('Skipped ' . $outputFile);
            return;
        }
        
        $output = $this->bufferTemplate($template, $data);
        $directory = dirname($outputFile);
        if (!file_exists($directory)) {
            $this->log('Creating directory ' . $directory);
            if (!$this->codegen->isDryRun()) {
                mkdir($directory, 0755, true);
            }
        }

        if ($overwrite && is_file($outputFile) && md5($output) == md5_file($outputFile)) {
            $this->debug('No changes ' . $outputFile . ' from template ' . $this->getTemplateFile($template));
            return;
        }

        $this->log((file_exists($outputFile) ? 'Overwriting' : 'Creating') . ' ' . $outputFile . ' from template ' . $template);
        if (!$this->codegen->isDryRun()) {
            file_put_contents($outputFile, $output);
        }
    }

    protected function getTemplateFile($name) {
        if (is_file($name)) {
            return $name;
        }
        if (isset($this->templateOverrides[$name])) {
            $file = $this->templateOverrides[$name];
        } else {
            $file = $this->codegen->getTemplatePath() . '/' . $name . '.php';
        }
        if (is_file($file)) {
            return $file;
        }
        $file = __DIR__ . '/../../templates/' . $this->name . '/' . $name . '.php';
        assert(is_file($file), 'Could not find template file: ' . $name . ' tried: ' . $file);
        return $file;
    }

    protected function createFiles(array $files) {
        foreach ($files as $file) {
            $directory = dirname($file);
            if (!file_exists($directory)) {
                $this->log('Creating directory ' . $directory);
                if (!$this->codegen->isDryRun()) {
                    mkdir($directory, 0755, true);
                }
            }
            if (!file_exists($file)) {
                $this->log('Creating file ' . $file);
                if (!$this->codegen->isDryRun()) {
                    file_put_contents($file, '');
                }
            }
        }
    }

    protected function log($message) {
        // @todo inject a logger
        echo ($this->codegen->isDryRun() ? '[DRY RUN] ' : '') . $message . PHP_EOL;
    }

    protected function debug($message) {
        if ($this->getCodegen()->isDebug()) {
            echo ($this->codegen->isDryRun() ? '[DRY RUN] ' : '') . $message . PHP_EOL;
        }
    }
    
    public function getCodegen() {
        return $this->codegen;
    }

    public function setCodegen($codegen) {
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
}
