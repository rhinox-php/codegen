<?php

namespace Rhino\Codegen\Cli\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenReverse extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('gen:reverse')
            ->setDescription('Reverse engineer database to Codegen XML');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'));
        $reverse = new \Rhino\Codegen\Process\ReverseMySql($codegen);
        $reverse->getXml();
        return 0;
    }
}
