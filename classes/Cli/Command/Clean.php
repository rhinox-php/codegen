<?php

namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Clean extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('clean')
            ->setDescription('Delete all generated files (that are recorded in the manifest)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Delete even if hashes don\'t match');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'), $input->getOption('force'))
            ->clean();
        return 0;
    }
}
