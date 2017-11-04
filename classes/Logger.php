<?php
namespace Rhino\Codegen;

trait Logger
{
    public function log()
    {
        if (PHP_SAPI === 'cli') {
            echo implode(' ', func_get_args()) . PHP_EOL;
        }
    }
}
