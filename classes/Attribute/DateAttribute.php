<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class DateAttribute extends Attribute
{
    public function getType()
    {
        return 'date';
    }
}
