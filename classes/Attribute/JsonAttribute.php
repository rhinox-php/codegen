<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class JsonAttribute extends Attribute
{
    public function getType()
    {
        return 'json';
    }
}
