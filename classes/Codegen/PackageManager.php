<?php
namespace Rhino\Codegen\Codegen;

class PackageManager {
    protected $codegen;
    protected $dependencies = [];
    protected $devDependencies = [];

    public function __construct(\Rhino\Codegen\Codegen $codegen) {
        $this->codegen = $codegen;
    }

    protected function loadJsonFile(string $file) {
        $file = $this->codegen->getFile($file);
        if (!is_file($file)) {
            $this->codegen->log('Could not find file ' . $file);
            return [];
        }
        $contents = file_get_contents($file);
        if (!$contents) {
            $this->codegen->log('Package file was empty ' . $file);
            return [];
        }
        $json = json_decode($contents, true);
        if ($json === false) {
            throw new \Exception('Could not parse JSON ' . $file);
        }
        return $json;
    }

    public function writeJsonFile(string $file, $json) {
        $file = $this->codegen->getFile($file);
        $this->codegen->writeFile($file, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function addDependency($name, $version) {
        $this->dependencies[$name] = $version;
    }

    public function addDevDependency($name, $version) {
        $this->devDependencies[$name] = $version;
    }
}
