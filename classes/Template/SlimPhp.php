<?php
namespace Rhino\Codegen\Template;

abstract class SlimPhp extends Template {
    protected $name = 'slim-php';
    protected $generatedNamespace = null;
    protected $implementedNamespace = null;

    public function getGeneratedNamespace(): string {
        return $this->generatedNamespace;
    }

    public function setGeneratedNamespace(string $generatedNamespace): self {
        $this->generatedNamespace = $generatedNamespace;
        return $this;
    }

    public function getImplementedNamespace(): string {
        return $this->implementedNamespace;
    }

    public function setImplementedNamespace(string $implementedNamespace): self {
        $this->implementedNamespace = $implementedNamespace;
        return $this;
    }
}
