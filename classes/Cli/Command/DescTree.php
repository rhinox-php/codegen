<?php
namespace Rhino\Codegen\Cli\Command;

use Rhino\Codegen\Codegen;
use Rhino\Codegen\Entity;
use Rhino\Codegen\Process\Description;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class DescTree extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('desc:tree')
            ->setDescription('Describe entity tree')
            ->addArgument('entity', InputArgument::REQUIRED, 'List entities.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $codegen = $this->getCodegen($input->getOption('schema'), !$input->getOption('execute'), $input->getOption('debug'));

        $filteredEntity = strtolower($input->getArgument('entity'));

        foreach ($codegen->getEntities() as $entity) {
            if ($filteredEntity != strtolower($entity->getClassName())) {
                continue;
            }

            $output->writeln($entity->getClassName());
            $output->writeln('Attributes:');

            $this->outputAttributes($output, $entity, 1);

            $output->writeln('Relationships:');
            $relationships = $entity->getRelationships();
            usort($relationships, function($a, $b) {
                return strnatcasecmp($a->getName(), $b->getName());
            });
            foreach ($relationships as $relationship) {
                if ($entity == $relationship->getFrom()) {
                    $output->writeln('  - ' . $relationship->getName());
                    $this->outputAttributes($output, $relationship->getTo(), 2);
                }
            }

            $output->writeln('');
        }
    }

    private function outputAttributes(OutputInterface $output, Entity $entity, int $indent) {
        $attributes = $entity->getAttributes();
        usort($attributes, function($a, $b) {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });
        foreach ($attributes as $attribute) {
            $output->writeln(\str_repeat('  ', $indent) . '- ' . $attribute->getLabel());
        }
    }
}
