<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class PasswordAttribute extends Attribute
{
    public function getType()
    {
        return 'password';
    }
}
