<?php
namespace Rhino\Codegen\Attribute;

use Rhino\Codegen\Attribute;

class ObjectAttribute extends Attribute
{
    protected $class;

    public function getType()
    {
        return 'object';
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass($value): self
    {
        $this->class = $value;
        return $this;
    }
}
