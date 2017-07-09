<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class ObjectAttribute extends Attribute {

    // Properties
    protected $class;

    public function getType() {
        return 'object';
    }

    // Attribute accessors
    public function getClass() {
        return $this->class;
    }

    public function setClass($value) {
        $this->class = $value;
        return $this;
    }
}
