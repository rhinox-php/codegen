<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbMigrate extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('db:migrate')
            ->setDescription('Generate migrations')
            ->addOption('write', 'w', InputOption::VALUE_NONE, 'Write SQL migration file.')
            ->addOption('run', 'r', InputOption::VALUE_NONE, 'Run migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'))
            ->dbMigrate($input->getOption('write'), $input->getOption('run'));
    }
}
