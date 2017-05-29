<?php
namespace Rhino\Codegen\Template;

abstract class Generic extends Template {

    protected $name = 'generic';
    protected $generatedNamespace = null;
    protected $implementedNamespace = null;

    public function getGeneratedNamespace() {
        return $this->generatedNamespace;
    }

    public function setGeneratedNamespace($generatedNamespace) {
        $this->generatedNamespace = $generatedNamespace;
        return $this;
    }

    public function getImplementedNamespace() {
        return $this->implementedNamespace;
    }

    public function setImplementedNamespace($implementedNamespace) {
        $this->implementedNamespace = $implementedNamespace;
        return $this;
    }
}
