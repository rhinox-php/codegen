<?php

namespace Rhino\Codegen\Hook;

use Rhino\Codegen\TempFile;

class RemoveDoubleLines extends Hook
{
    protected $hook = 'format';

    public function process(TempFile $tempFile): array
    {
        $contents = file_get_contents($tempFile->getPath());
        $contents = preg_replace('/\n\n+/', "\n\n", $contents);
        file_put_contents($tempFile->getPath(), $contents);
        return [$tempFile];
    }
}
