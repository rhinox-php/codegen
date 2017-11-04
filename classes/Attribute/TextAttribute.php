<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class TextAttribute extends Attribute
{
    public function getType()
    {
        return 'text';
    }
}
