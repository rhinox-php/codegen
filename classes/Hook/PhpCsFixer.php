<?php
namespace Rhino\Codegen\Hook;

use Rhino\Codegen\Template\OutputFile;

class PhpCsFixer extends Hook {
    protected $hook = 'gen:write';

    public function process(OutputFile $outputFile): array {
        \Rhino\Codegen\FormatPhp::formatFile($outputFile->getPath());
        return [$outputFile];
    }
}
