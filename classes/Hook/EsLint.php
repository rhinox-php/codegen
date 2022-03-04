<?php

namespace Rhino\Codegen\Hook;

use Rhino\Codegen\TempFile;

class EsLint extends Hook
{
    protected $hook = 'format';

    public function __construct(
        private $eslintPath,
        private $configPath,
    ) {
    }

    public function process(TempFile $tempFile): array
    {
        switch ($tempFile->getExtension()) {
            case 'js':
            case 'jsx':
            case 'ts':
            case 'tsx':
                assert(is_file($this->eslintPath), new \Exception('Could not find eslint: ' . $this->eslintPath));
                assert(is_file($this->configPath), new \Exception('Could not find eslint config file: ' . $this->configPath));
                passthru($this->eslintPath . ' --config ' . $this->configPath . ' --fix ' . $tempFile->getPath());
        }
        return [$tempFile];
    }
}
