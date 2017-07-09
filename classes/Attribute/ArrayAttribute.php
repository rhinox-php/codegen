<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class ArrayAttribute extends Attribute {
    public function getType() {
        return 'array';
    }
}