<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class StringAttribute extends Attribute
{
    public function getType()
    {
        return 'string';
    }
}
