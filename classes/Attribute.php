<?php
namespace Rhino\Codegen;

class Attribute {

    use Inflector;

    protected $name;
    protected $propertyName;
    protected $methodName;
    protected $columnName;
    protected $nullable = true;

    /**
     * @var bool If true accessors will be generated.
     */
    protected $hasAccessors = true;

    /**
     * @var bool If true the attribute will be included in JSON serialization.
     */
    protected $jsonSerialize = true;

    /**
     * @var bool If true the attribute is a foreign key.
     */
    protected $isForeignKey = false;

    public function getName(): string {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getPropertyName(): string {
        return $this->propertyName ?: $this->camelize($this->getName(), true);
    }

    public function setPropertyName($propertyName) {
        $this->propertyName = $propertyName;
        return $this;
    }

    public function getMethodName(): string {
        return $this->methodName ?: $this->camelize($this->getName());
    }

    public function setMethodName($methodName) {
        $this->methodName = $methodName;
        return $this;
    }

    public function getColumnName(): string {
        return $this->columnName ?: $this->underscore($this->getName());
    }

    public function setColumnName($columnName) {
        $this->columnName = $columnName;
        return $this;
    }

    public function getGetterName() {
        if ($this->is(['Bool'])) {
            return 'is' . $this->getMethodName();
        }
        return 'get' . $this->getMethodName();
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
    
    /**
     * @return bool
     */
    public function hasAccessors(): bool
    {
        return $this->hasAccessors;
    }

    /**
     * @param bool $hasAccessors
     * @return Attribute
     */
    public function setHasAccessors(bool $hasAccessors): Attribute
    {
        $this->hasAccessors = $hasAccessors;
        return $this;
    }

    /**
     * @return bool
     */
    public function getJsonSerialize(): bool
    {
        return $this->jsonSerialize;
    }

    /**
     * @param bool $jsonSerialize
     * @return Attribute
     */
    public function setJsonSerialize(bool $jsonSerialize): Attribute
    {
        $this->jsonSerialize = $jsonSerialize;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForeignKey(): bool
    {
        return $this->isForeignKey;
    }

    /**
     * @param bool $isForeignKey
     * @return Attribute
     */
    public function setIsForeignKey(bool $isForeignKey): Attribute
    {
        $this->isForeignKey = $isForeignKey;
        return $this;
    }
}
