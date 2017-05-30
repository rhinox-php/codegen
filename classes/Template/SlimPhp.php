<?php
namespace Rhino\Codegen\Template;

abstract class SlimPHP extends Template {

    protected $implementedNamespace = null;
    protected $name = 'slim-php';
    protected $namespace = null;
    protected $template = null;

    public function getNamespace() {
        return $this->namespace;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        return $this;
    }

    public function getImplementedNamespace(): ?string {
        return $this->implementedNamespace ?: $this->getNamespace();
    }

    public function setImplementedNamespace($implementedNamespace) {
        $this->implementedNamespace = $implementedNamespace;
        return $this;
    }

    public function getTemplate(): string {
        return $this->template;
    }

    public function setTemplate(string $template) {
        $this->template = $template;
        return $this;
    }

}
