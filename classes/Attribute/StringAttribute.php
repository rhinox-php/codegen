<?php
namespace Rhino\Codegen\Attribute;

class StringAttribute {
    
    protected $name;
    protected $entity;
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }
    
}
