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

    protected function getCodegen(?string $schema, bool $dryRun, bool $debug, bool $force = false): \Rhino\Codegen\Codegen
    {
        if (!$schema) {
            $currentDirectory = getcwd() . '/codegen/';
            while (is_dir($currentDirectory) ) {
                if (is_file($currentDirectory . '/codegen.php')) {
                    $schema = $currentDirectory . '/codegen.php';
                    break;
                }
                if (dirname($currentDirectory) == $currentDirectory) {
                    break;
                }
                $currentDirectory = dirname($currentDirectory);
            }
        }
        if (!is_file($schema)) {
            throw new \Exception('Could not find codegen schema file: ' . $schema);
        }
        $codegen = require $schema;
        $codegen->setDryRun($dryRun);
        $codegen->setForce($force);
        if ($debug) {
            $codegen->setOutputLevel(Codegen::OUTPUT_LEVEL_DEBUG);
        }
        return $codegen;
    }
}
