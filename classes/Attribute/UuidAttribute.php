<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class UuidAttribute extends Attribute
{
    protected $nullable = false;

    public function getType()
    {
        return 'uuid';
    }
}
