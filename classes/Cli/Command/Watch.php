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

class Watch extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('watch')
            ->setDescription('Watch code for changes and trigger generation automatically')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Sleep time (microseconds)', 2)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force regenerating all files')
            ->addOption('overwrite', 'o', InputOption::VALUE_NONE, 'Overwrite local changes to files')
            ->addOption('filter', 'i', InputOption::VALUE_REQUIRED, 'Filter entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'), $input->getOption('force'), $input->getOption('overwrite'), $input->getOption('filter'));
        $watcher = new \Rhino\Codegen\Watch\Watcher(function ($changed, $files) use ($input, $output, $codegen) {
            $codegen->log('Watched files', count($files));
            $codegen->log('Files changed', array_slice($changed, 0, 3));

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
            if ($input->getOption('force')) {
                $commandLine[] = '--force';
            }
            if ($input->getOption('overwrite')) {
                $commandLine[] = '--overwrite';
            }
            if ($input->getOption('filter')) {
                $commandLine[] = '--filter=' . $input->getOption('filter');
            }
            $process = new Process($commandLine);
            $process->start();

            $process->wait(function ($type, $buffer) use ($output) {
                if (Process::ERR === $type) {
                    $output->getErrorOutput()->write($buffer);
                } else {
                    $output->write($buffer);
                }
            });
        }, $input->getOption('sleep'));
        foreach ($codegen->getWatchDirectories() as $directory) {
            $watcher->addDirectory($directory);
        }
        $watcher->start();
        return 0;
    }
}
