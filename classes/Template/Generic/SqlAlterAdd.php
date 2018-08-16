<?php
namespace Rhino\Codegen\Template\Generic;

class SqlAlterAdd extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/sql/full/alter-table-add', 'src/sql/alter/add/' . $entity->table . '.sql', [
                'entity' => $entity,
            ]);
        }
    }
}
