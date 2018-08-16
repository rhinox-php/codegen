<?php
namespace Rhino\Codegen\Template\Generic;

class SqlAlterChange extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/sql/full/alter-table-change', 'src/sql/alter/change/' . $entity->table . '.sql', [
                'entity' => $entity,
            ]);
        }
    }
}
