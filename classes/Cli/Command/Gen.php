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
            ->setDescription('Generate code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'))
            ->generate();
    }
}
