<?php
namespace Rhino\Codegen\Hook;

class Callback extends Hook
{
    protected $hook;
    protected $callback;

    public function __construct(string $hook, callable $callback)
    {
        $this->hook = $hook;
        $this->callback = $callback;
    }
    
    public function process()
    {
    }
}
