<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class BoolAttribute extends Attribute
{
    public function getType()
    {
        return 'bool';
    }
}
