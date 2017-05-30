<?php
namespace Rhino\Codegen\Codegen\Web;

class Composer extends \Rhino\Codegen\Codegen\PackageManager {
    protected $repositories = [];

    public function generate() {
        $json = $this->loadJsonFile('composer.json');
        $json['minimum-stability'] = 'dev';
        $json['prefer-stable'] = true;
        if (!empty($this->dependencies)) {
            if (!isset($json['require'])) {
                $json['require'] = [];
            }
            foreach ($this->dependencies as $name => $version) {
                $json['require'][$name] = $version;
            }
            ksort($json['require']);
        }
        if (!empty($this->devDependencies)) {
            if (!isset($json['require-dev'])) {
                $json['require-dev'] = [];
            }
            foreach ($this->devDependencies as $name => $version) {
                $json['require-dev'][$name] = $version;
            }
            ksort($json['require-dev']);
        }
        if (!empty($this->repositories)) {
            if (!isset($json['repositories'])) {
                $json['repositories'] = [];
            }
            foreach ($this->repositories as $repository) {
                $json['repositories'][] = $repository;
            }
            $json['repositories'] = array_unique($json['repositories'], SORT_REGULAR);
            $json['repositories'] = array_values($json['repositories']);
        }
        $this->writeJsonFile('composer.json', $json);
    }

    public function addRepository($repository) {
        $this->repositories[] = $repository;
    }
}
