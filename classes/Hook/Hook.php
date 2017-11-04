<?php
namespace Rhino\Codegen\Hook;

abstract class Hook
{
    protected $hook;

    // public function process(...$args): array {
    //     return $args;
    // }

    public function getHook()
    {
        assert(strlen($this->hook) > 0, new \UnexpectedValueException('Expected hook name to be a non empty string.'));
        return $this->hook;
    }
}
