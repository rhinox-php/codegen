<?php
namespace Rhino\Codegen\Hook;

use Rhino\Codegen\TempFile;

class Callback extends Hook
{
    protected $hook;
    protected $callback;

    public function __construct(string $hook, callable $callback)
    {
        $this->hook = $hook;
        $this->callback = $callback;
    }

    public function process(TempFile $tempFile): array
    {
        return ($this->callback)($tempFile);
    }
}
