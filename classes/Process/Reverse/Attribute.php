<?php

namespace Rhino\Codegen\Process\Reverse;

class Attribute
{
    protected $name;
    protected $type;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }
}
