<?php
namespace Rhino\Codegen\Process;

use Rhino\Codegen\Codegen;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Helper\Table;

class Description
{
    protected $codegen;

    public function __construct(Codegen $codegen) {
        $this->codegen = $codegen;
    }

    public function describe()
    {
        $output = new BufferedOutput();
        foreach ($this->codegen->node->children('entity') as $entity) {
            yield 'Entity:';
            (new Table($output))
                ->setHeaders(['Name', 'Class Name', 'Property Name'])
                ->setRows([
                    [$entity->name, $entity->class, $entity->property],
                ])
                ->render();
            yield $output->fetch();
            yield 'Attributes:';
            $rows = [];
            foreach ($entity->children as $attribute) {
                $rows[] = [
                    $attribute->name,
                    $attribute->label,
                    $attribute->property,
                    $attribute->type,
                    // json_encode($attribute->meta->properties),
                ];
            }
            (new Table($output))
                ->setHeaders(['Name', 'Label', 'Property Name', 'Type'])
                ->setRows($rows)
                ->render();
            yield $output->fetch();
        }
    }

    public function list()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            yield $entity->class;
        }
    }
}
