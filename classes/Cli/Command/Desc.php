<?php
namespace Rhino\Codegen\Cli\Command;

use Rhino\Codegen\Codegen;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Desc extends AbstractCommand {
    protected function configure() {
        parent::configure();
        $this->setName('desc')
            ->setDescription('Generate migrations')
            ->addOption('list', 'l', InputOption::VALUE_NONE, 'List entities.')
            ->addOption('full', 'f', InputOption::VALUE_NONE, 'Output full descriptions.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'));

        if ($input->getOption('full')) {
            $this->describe($codegen, $output);
        }

        if ($input->getOption('list')) {
            $this->list($codegen, $output);
        }
    }

    protected function describe(Codegen $codegen, OutputInterface $output) {
        foreach ($codegen->getEntities() as $entity) {
            $output->writeln('Entity:');
            (new Table($output))
                ->setHeaders(['Class Name', 'Property Name'])
                ->setRows([
                    [$entity->getClassName(), $entity->getPropertyName()],
                ])
                ->render();
            $output->writeln('Attributes:');
            $rows = [];
            foreach ($entity->getAttributes() as $attribute) {
                $rows[] = [$attribute->getName(), $attribute->getPropertyName(), $attribute->getType()];
            }
            (new Table($output))
                ->setHeaders(['Class Name', 'Property Name', 'Type'])
                ->setRows($rows)
                ->render();
            $output->writeln('');
        }
    }

    protected function list(Codegen $codegen, OutputInterface $output) {
        foreach ($codegen->getEntities() as $entity) {
            $output->writeln($entity->getClassName());
        }
    }
}
