<?php
namespace Rhino\Codegen\Cli\Command;

use const Rhino\Codegen\ROOT;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\PhpExecutableFinder;

class Watch extends AbstractCommand {
    protected function configure() {
        $this->setName('watch')
            ->setDescription('Watch code for changes and trigger generation automatically')
            ->addOption('execute', 'x', InputOption::VALUE_NONE, 'Execute code generation (otherwise dry run).')
            ->addOption('schema', 's', InputOption::VALUE_REQUIRED, 'Codegen schema file to load.', 'codegen.php')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable debug output');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'));
        $watcher = new \Rhino\Codegen\Watch\Watcher(function() use($input, $output) {
            $executableFinder = new PhpExecutableFinder();
            if (false === $commandLine = $executableFinder->find(false)) {
                $commandLine = null;
            } else {
                $commandLine = array_merge([$commandLine], $executableFinder->findArguments());
            }

            $commandLine[] = ROOT . '/bin/rhino-codegen.php';
            $commandLine[] = 'gen';
            if ($input->getOption('execute')) {
                $commandLine[] = '--execute';
            }
            if ($input->getOption('schema')) {
                $commandLine[] = '--schema=' . $input->getOption('schema');
            }
            if ($input->getOption('debug')) {
                $commandLine[] = '--debug';
            }
            $process = new Process($commandLine);
            $process->start();

            $process->wait(function ($type, $buffer) use($output) {
                if (Process::ERR === $type) {
                    $output->getErrorOutput()->write($buffer);
                } else {
                    $output->write($buffer);
                }
            });
        });
        $watcher->addDirectory(__DIR__ . '/../../../');
        $watcher->addDirectory('.');
        $watcher->start();
    }
}
