<?php
namespace Rhino\Codegen;

class Entity {
    
    protected $name;
    protected $attributes = [];
    protected $relationships = [];
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function setAttributes($attributes) {
        $this->attributes = $attributes;
        return $this;
    }

    public function addAttribute($attribute) {
        $this->attributes[] = $attribute;
        return $this;
    }

    public function getRelationships() {
        return $this->relationships;
    }

    public function setRelationships($relationships) {
        $this->relationships = $relationships;
        return $this;
    }
    
    public function addRelationship($relationship) {
        $this->relationships[] = $relationship;
        return $this;
    }
    
}
