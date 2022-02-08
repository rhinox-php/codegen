<?php
namespace Rhino\Codegen\Hook;

use Rhino\Codegen\Template\OutputFile;

class RemoveDoubleLines extends Hook
{
    protected $hook = 'gen:write';

    public function process(OutputFile $outputFile): array
    {
        $contents = file_get_contents($outputFile->getPath());
        $contents = preg_replace('/\n\n+/', "\n\n", $contents);
        file_put_contents($outputFile->getPath(), $contents);
        return [$outputFile];
    }
}
