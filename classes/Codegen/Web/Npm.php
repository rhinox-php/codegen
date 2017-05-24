<?php
namespace Rhino\Codegen\Codegen\Web;

class Npm extends \Rhino\Codegen\Codegen\PackageManager {
    public function generate() {
        $json = $this->loadJsonFile('package.json');
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
        $this->writeJsonFile('package.json', $json);
    }
}
