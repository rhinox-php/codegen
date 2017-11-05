<?php
namespace Rhino\Codegen\Template\Generic;

class Bin extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface
{
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate()
    {
        yield BinServer::class;
        yield BinLint::class;
        yield BinTest::class;
    }
}
