<?php
namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Gen extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('gen')
            ->setDescription('Generate code')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite local changes to files');;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'), $input->getOption('force'))
            ->generate();
    }
}
