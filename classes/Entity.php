<?php
namespace Rhino\Codegen;

class Entity {
    use Inflector;

    protected $name;
    protected $authentication = false;
    protected $attributes = [];
    protected $relationships = [];

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
        return $this;
    }

    public function getClassName(): string {
        return $this->camelize($this->getName());
    }

    public function getPluralClassName(): string {
        return $this->pluralize($this->getClassName());
    }

    public function getTableName(): string {
        return $this->underscore($this->getName());
    }

    public function getPropertyName(): string {
        $inflector = $this->getInflector();
        $propertyName = $inflector->pluralize($this->getName());
        return $inflector->camelize($propertyName, true);
    }

    public function getFileName(): string {
        return $this->hyphenate($this->getName());
    }

    public function getRouteName(): string {
        return $this->hyphenate($this->getName());
    }

    public function getPluralRouteName(): string {
        return $this->pluralize($this->getRouteName());
    }

    public function getLabel(): string {
        return $this->humanize($this->getName());
    }

    public function getPluralLabel(): string {
        return $this->pluralize($this->getLabel());
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

    public function hasAuthentication() {
        return $this->authentication;
    }

    public function setAuthentication($authentication) {
        $this->authentication = $authentication;
        return $this;
    }

}
