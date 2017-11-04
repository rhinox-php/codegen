<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class DecimalAttribute extends Attribute
{
    public function getType()
    {
        return 'decimal';
    }
}
