<?php
namespace Rhino\Codegen\Hook;

use Rhino\Codegen\Template\OutputFile;

class PhpCsFixer extends Hook
{
    protected $hook = 'gen:write';

    public function process(OutputFile $outputFile): array
    {
        if ($outputFile->getExtension() === 'php') {
            \Rhino\Codegen\FormatPhp::formatFile($outputFile->getPath());
        }
        return [$outputFile];
    }
}
