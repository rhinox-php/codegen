<?php
namespace Rhino\Codegen\Template\Generic;

class SqlAlterIndex extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('sql/full/alter-table-index', 'src/sql/alter/indexes.sql', [
            'entities' => $this->codegen->getEntities(),
        ]);
    }
}
