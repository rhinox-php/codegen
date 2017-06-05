<?php
namespace Rhino\Codegen\Template\Generic;

class SqlAlterChange extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('sql/full/alter-table-change', 'src/sql/alter/change/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);
        }
    }
}
