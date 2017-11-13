<?php
namespace Rhino\Codegen\Template\Generic;

class Bin extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface
{
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate()
    {
        yield $this->aggregateClass(BinServer::class);
        yield $this->aggregateClass(BinLint::class);
        yield $this->aggregateClass(BinTest::class);
    }
}
