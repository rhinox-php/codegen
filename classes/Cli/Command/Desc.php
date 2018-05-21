<?php
namespace Rhino\Codegen\Cli\Command;

use Rhino\Codegen\Codegen;
use Rhino\Codegen\Process\Description;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Desc extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('desc')
            ->setDescription('Generate migrations')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'List entities.')
            ->addOption('full', 'f', InputOption::VALUE_NONE, 'Output full descriptions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'));
        $description = new Description($codegen);

        $type = 'full';
        if ($input->getOption('list')) {
            $type = 'list';
        }

        if ($type == 'full') {
            foreach ($description->describe() as $line) {
                $output->writeln($line);
            }
        }

        if ($type == 'list') {
            foreach ($description->list() as $line) {
                $output->writeln($line);
            }
        }
    }
}
