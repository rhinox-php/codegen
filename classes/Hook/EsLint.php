<?php

namespace Rhino\Codegen\Hook;

use Rhino\Codegen\Template\OutputFile;

class EsLint extends Hook
{
    protected $hook = 'gen:write';

    public function __construct(
        private $eslintPath,
        private $basePath,
    )
    {

    }

    public function process(OutputFile $outputFile): array
    {
        switch ($outputFile->getExtension()) {
            case 'js':
            case 'jsx':
            case 'ts':
            case 'tsx':
                assert(is_file($this->eslintPath), new \Exception('Could not find eslint.'));
                $cwd = getcwd();
                chdir($this->basePath);
                passthru($this->eslintPath . ' --fix ' . $outputFile->getPath());
                chdir($cwd);
        }
        return [$outputFile];
    }
}
