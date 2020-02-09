<?php

namespace Rhino\Codegen\Process\Reverse;

class Node
{
    public $name;
    public $attributes = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setAttribute(string $name, Attribute $value)
    {
        $this->attributes[$name] = $value;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
    public function getName()
    {
        return $this->name;
    }
}
