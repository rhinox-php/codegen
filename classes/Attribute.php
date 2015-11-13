<?php
namespace Rhino\Codegen;

class Attribute {

    use Inflector;

    protected $name;

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
        return $this;
    }

    public function getPropertyName(): string {
        return $this->camelize($this->getName(), true);
    }

    public function getMethodName(): string {
        return $this->camelize($this->getName());
    }

    public function getColumnName(): string {
        return $this->underscore($this->getName());
    }

    public function getLabel(): string {
        $inflector = $this->getInflector();
        $label = $inflector->underscore($this->getName());
        return $inflector->humanize($label, true);
    }

}
