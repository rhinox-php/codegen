<?php

namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Rhino\Codegen\Stub;

class GenStub extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('gen:stub')
            ->setDescription('Generate code')
            ->addArgument('file', InputArgument::REQUIRED, 'Class file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'), $input->getOption('force'), $input->getOption('overwrite'), $input->getOption('filter'));
        $stub = new Stub($codegen, $input->getArgument('file'));
        echo $stub->generate();
        return 0;
    }
}
