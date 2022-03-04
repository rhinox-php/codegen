<?php

namespace Rhino\Codegen\Cli\Command;

use Rhino\Codegen\Codegen;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends \Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->addOption('execute', 'x', InputOption::VALUE_NONE, 'Execute code generation (otherwise dry run).')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', null)
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable debug output');
    }

    protected function getCodegen(?string $schemaFileOverride, bool $dryRun, bool $debug, bool $force = false, bool $overwrite = false, ?string $filter = ''): \Rhino\Codegen\Codegen
    {
        $schemaFile = $this->getSchemaFile($schemaFileOverride);
        /** @var Codegen */
        $codegen = require $schemaFile;
        $codegen->setSchemaFile(realpath($schemaFile));
        $codegen->setDryRun($dryRun);
        $codegen->setForce($force);
        $codegen->setOverwrite($overwrite);
        $codegen->setFilter($filter);
        if ($debug) {
            $codegen->setOutputLevel(Codegen::OUTPUT_LEVEL_DEBUG);
        }
        return $codegen;
    }

    protected function getSchemaFile(?string $schemaFileOverride): string
    {
        if ($schemaFileOverride) {
            if (!is_file($schemaFileOverride)) {
                throw new \Exception('Could not find codegen schema file: ' . $schemaFileOverride);
            }
            return $schemaFileOverride;
        }

        $schemaFile = null;
        $currentDirectory = getcwd();
        do {
            if (is_file($currentDirectory . '/codegen.php')) {
                $schemaFile = $currentDirectory . '/codegen.php';
                break;
            }
            if (is_file($currentDirectory . '/codegen/codegen.php')) {
                $schemaFile = $currentDirectory . '/codegen/codegen.php';
                break;
            }
            if (dirname($currentDirectory) == $currentDirectory) {
                break;
            }
            $currentDirectory = dirname($currentDirectory);
        } while (is_dir($currentDirectory));

        if (!$schemaFile) {
            throw new \Exception('Could not find codegen schema file');
        }
        return $schemaFile;
    }
}
