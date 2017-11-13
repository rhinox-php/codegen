<?php
namespace Rhino\Codegen\Template\Generic;

class Sql extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface
{
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate()
    {
        yield $this->aggregateClass(SqlFull::class);
        yield $this->aggregateClass(SqlMigrate::class);
        yield $this->aggregateClass(SqlAlterChange::class);
        yield $this->aggregateClass(SqlAlterAdd::class);
        yield $this->aggregateClass(SqlAlterIndex::class);
    }
}
