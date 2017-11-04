<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class DateTimeAttribute extends Attribute
{
    public function getType()
    {
        return 'date';
    }
}
