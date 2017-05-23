<?php
namespace Rhino\Codegen\Codegen\Web;

class Npm {
    protected $codegen;
    protected $dependencies = [];
    protected $devDependencies = [];

    public function __construct(\Rhino\Codegen\Codegen $codegen) {
        $this->codegen = $codegen;
    }

    public function generate() {
        $file = $this->codegen->getFile('package.json');
        if (!is_file($file)) {
            $this->codegen->debug('Could not find file', $file);
            return;
        }
        $contents = file_get_contents($file);
        if (!$contents) {
            $this->codegen->debug('Package file was empty', $file);
            return;
        }
        $json = json_decode($contents, true);
        if (!$json) {
            $this->codegen->debug('Could not parse JSON', $file);
            return;
        }
        if (!empty($this->dependencies)) {
            if (!isset($json['dependencies'])) {
                $json['dependencies'] = [];
            }
            foreach ($this->dependencies as $name => $version) {
                $json['dependencies'][$name] = $version;
            }
            ksort($json['dependencies']);
        }
        if (!empty($this->devDependencies)) {
            if (!isset($json['devDependencies'])) {
                $json['devDependencies'] = [];
            }
            foreach ($this->devDependencies as $name => $version) {
                $json['devDependencies'][$name] = $version;
            }
            ksort($json['devDependencies']);
        }
        $this->codegen->writeFile($file, json_encode($json, JSON_PRETTY_PRINT));
    }

    public function addDependency($name, $version) {
        $this->dependencies[$name] = $version;
    }

    public function addDevDependency($name, $version) {
        $this->devDependencies[$name] = $version;
    }
}
