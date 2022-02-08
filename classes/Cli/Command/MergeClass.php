<?php

namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MergeClass extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('merge:class')
            ->setDescription('Merge 2 class files into one.')
            ->addOption('file-1', null, InputOption::VALUE_REQUIRED, 'File 1')
            ->addOption('file-2', null, InputOption::VALUE_REQUIRED, 'File 2');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'));
        $file1 = $input->getOption('file-1');
        $file2 = $input->getOption('file-2');
        \Rhino\Codegen\MergeClass::mergeFiles($codegen, $file1, $file2);
        return 0;
    }
}
