<?php
namespace Rhino\Codegen;

class Entity {
    use Inflector;

    protected $name;
    protected $attributes = [];
    protected $relationships = [];

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
        return $this;
    }

    public function getPluralName(): string {
        return $this->getInflector()->pluralize($this->getName());
    }

    public function getTableName(): string {
        return $this->getInflector()->underscore($this->getName());
    }

    public function getPropertyName(): string {
        $inflector = $this->getInflector();
        $propertyName = $inflector->pluralize($this->getName());
        return $inflector->camelize($propertyName, true);
    }

    public function getFileName(): string {
        $inflector = $this->getInflector();
        $propertyName = $inflector->underscore($this->getName());
        return $inflector->dasherize($propertyName);
    }

    public function getRouteName(): string {
        $inflector = $this->getInflector();
        $propertyName = $inflector->underscore($this->getName());
        return $inflector->dasherize($propertyName);
    }

    public function getPluralRouteName(): string {
        $inflector = $this->getInflector();
        $propertyName = $inflector->underscore($this->getName());
        $propertyName = $inflector->pluralize($propertyName);
        return $inflector->dasherize($propertyName);
    }

    public function getLabel(): string {
        $inflector = $this->getInflector();
        $label = $inflector->underscore($this->getName());
        return $inflector->humanize($label, true);
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function setAttributes(array $attributes) {
        $this->attributes = $attributes;
        return $this;
    }

    public function addAttribute(Attribute $attribute) {
        $this->attributes[] = $attribute;
        return $this;
    }

    public function getRelationships(): array {
        return $this->relationships;
    }

    public function setRelationships(array $relationships) {
        $this->relationships = $relationships;
        return $this;
    }

    public function addRelationship(Relationship $relationship) {
        $this->relationships[] = $relationship;
        return $this;
    }

    public function getCodegen(): Codegen {
        return $this->codegen;
    }

    public function setCodegen(Codegen $codegen) {
        $this->codegen = $codegen;
    }

}
