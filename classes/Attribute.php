<?php
namespace Rhino\Codegen;

class Attribute {

    use Inflector;

    protected $name;
    protected $nullable = true;

    public function getName(): string {
        return $this->name;
    }

    public function setName($name) {
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
    
    public function is(array $types): bool {
        foreach ($types as $type) {
            $type = 'Rhino\\Codegen\\Attribute\\' . $type . 'Attribute';
            if ($this instanceof $type) {
                return true;
            }
        }
        return false;
    }
    
    public function isNullable(): bool {
        return $this->nullable;
    }

    public function setNullable(bool $nullable) {
        $this->nullable = $nullable;
        return $this;
    }
}
