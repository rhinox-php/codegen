<?php
namespace Rhino\Codegen\Template\Laravel;

class Model extends \Rhino\Codegen\Template\Laravel implements \Rhino\Codegen\Template\AggregateInterface
{
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate()
    {
        yield ModelAbstract::class;
        yield ModelGenerated::class;
    }
}
