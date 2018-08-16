<?php
namespace Rhino\Codegen\Template\Generic;

class SqlFull extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\Interfaces\DatabaseReset
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/sql/full/create-table', 'src/sql/full/' . $entity->table . '.sql', [
                'entity' => $entity,
            ]);
        }
    }

    public function iterateDatabaseResetSql(): iterable
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            yield $this->bufferTemplate('generic/sql/full/create-table', [
                'entity' => $entity,
            ]);
        }
    }
}
