<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbReset extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('db:reset')
            ->setDescription('Reset table')
            ->addArgument('entity', InputArgument::OPTIONAL, 'Entity type to reset.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'))
            ->dbReset();
    }
}
