<?php
namespace Rhino\Codegen\Codegen\Web;

class Npm extends \Rhino\Codegen\Codegen\PackageManager {
    public function __construct(\Rhino\Codegen\Codegen $codegen) {
        parent::__construct($codegen);
        $this->codegen->gitIgnore->addIgnore('node_modules');
    }

    public function generate() {
        $empty = true;
        $json = $this->loadJsonFile('package.json');
        if (!empty($this->dependencies)) {
            if (!isset($json['dependencies'])) {
                $json['dependencies'] = [];
            }
            foreach ($this->dependencies as $name => $version) {
                $json['dependencies'][$name] = $version;
                $empty = false;
            }
            ksort($json['dependencies']);
        }
        if (!empty($this->devDependencies)) {
            if (!isset($json['devDependencies'])) {
                $json['devDependencies'] = [];
            }
            foreach ($this->devDependencies as $name => $version) {
                $json['devDependencies'][$name] = $version;
                $empty = false;
            }
            ksort($json['devDependencies']);
        }
        $this->writeJsonFile('package.json', $json, $empty);
    }
}
