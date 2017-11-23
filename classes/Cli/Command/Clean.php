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
            ->setDescription('Delete all generated file (that are recorded in the manifest)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'))
            ->clean();
    }
}
