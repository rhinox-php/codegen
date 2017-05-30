<?php
namespace Rhino\Codegen\Template\Generic;

class SqlAlterAdd extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('sql/full/alter-table-add', 'sql/alter/add/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);
        }
    }
}
