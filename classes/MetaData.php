<?php
namespace Rhino\Codegen;

class MetaData
{
    public $properties = [];

    public function bool(string $name, bool $default = false): bool {
        if (!isset($this->properties[$name])) {
            return $default;
        }
        return $this->properties[$name] === 'true';
    }
}
