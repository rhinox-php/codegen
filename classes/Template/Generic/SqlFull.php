<?php
namespace Rhino\Codegen\Template\Generic;

class SqlFull extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('sql/full/create-table', 'sql/full/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);
        }
    }
}
