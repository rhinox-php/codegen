<?php
namespace Rhino\Codegen\Hook;

class PhpCsFixer extends Hook {
    protected $hook = 'gen:write';

    public function process(string $outputFile): array {
        exec('php-cs-fixer fix ' . escapeshellarg($outputFile) . ' > /dev/null 2>&1 &');
        return [$outputFile];
    }
}
