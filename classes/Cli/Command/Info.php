<?php

namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Info extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('info')
            ->setDescription('List namespaces and paths');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'))
            ->codegenInfo();
        return 0;
    }
}
