<?php
namespace Rhino\Codegen\Template\Generic;

class SqlFull extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\Interfaces\DbReset {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('sql/full/create-table', 'src/sql/full/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);
        }
    }

    public function iterateSql() {
        foreach ($this->codegen->getEntities() as $entity) {
            yield $this->bufferTemplate('sql/full/create-table', [
                'entity' => $entity,
            ]);
        }
    }
}
