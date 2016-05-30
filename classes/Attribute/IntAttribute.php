<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class IntAttribute extends Attribute {
    public function getType() {
        return 'int';
    }
}
