<?php
namespace Rhino\Codegen\Template\Generic;

class Sql extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface
{
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate()
    {
        yield SqlFull::class;
        yield SqlMigrate::class;
        yield SqlAlterChange::class;
        yield SqlAlterAdd::class;
        yield SqlAlterIndex::class;
    }
}
