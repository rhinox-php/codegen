<?php
namespace Rhino\Codegen\Template\Generic;

class Model extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface
{
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate()
    {
        yield $this->aggregateClass(ModelAbstract::class);
        yield $this->aggregateClass(ModelGenerated::class);
        yield $this->aggregateClass(ModelInitial::class);
        yield $this->aggregateClass(ModelPdo::class);
        yield $this->aggregateClass(ModelSerializer::class);
        yield $this->aggregateClass(ModelTest::class);
    }
}
