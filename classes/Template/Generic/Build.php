<?php
namespace Rhino\Codegen\Template\Generic;

class Build extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface
{
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate()
    {
        yield $this->aggregateClass(Gulp::class);
    }
}
