<?php
namespace Rhino\Codegen\Codegen;

class PackageManager {
    protected $codegen;
    protected $dependencies = [];
    protected $devDependencies = [];

    protected function loadJsonFile(string $file) {
        $file = $this->codegen->getFile($file);
        if (!is_file($file)) {
            throw new \Exception('Could not find file ' . $file);
        }
        $contents = file_get_contents($file);
        if (!$contents) {
            throw new \Exception('Package file was empty ' . $file);
        }
        $json = json_decode($contents, true);
        if (!$json) {
            throw new \Exception('Could not parse JSON ' . $file);
        }
        return $json;
    }

    public function writeJsonFile(string $file, $json) {
        $file = $this->codegen->getFile($file);
        $this->codegen->writeFile($file, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function __construct(\Rhino\Codegen\Codegen $codegen) {
        $this->codegen = $codegen;
    }

    public function addDependency($name, $version) {
        $this->dependencies[$name] = $version;
    }

    public function addDevDependency($name, $version) {
        $this->devDependencies[$name] = $version;
    }
}
