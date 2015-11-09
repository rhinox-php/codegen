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

    public function getMethodName(): string {
        return $this->getInflector()->camelize($this->getName());
    }

    public function getColumnName(): string {
        return $this->getInflector()->underscore($this->getName());
    }

    public function getLabel(): string {
        $inflector = $this->getInflector();
        $label = $inflector->underscore($this->getName());
        return $inflector->humanize($label, true);
    }

}
