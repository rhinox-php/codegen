<?php
namespace Rhino\Codegen\Hook;

use Rhino\Codegen\TempFile;

class PhpCsFixer extends Hook
{
    protected $hook = 'format';

    public function process(TempFile $tempFile): array
    {
        if ($tempFile->getExtension() === 'php') {
            \Rhino\Codegen\FormatPhp::formatFile($tempFile->getPath());
        }
        return [$tempFile];
    }
}
