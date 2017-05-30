<?php
namespace Rhino\Codegen\Template\Generic;

class Sql extends \Rhino\Codegen\Template\Aggregate {

    public function aggregate() {
        yield (new SqlFull())->setPath($this->getPath());
        yield (new SqlAlterChange())->setPath($this->getPath());
        yield (new SqlAlterAdd())->setPath($this->getPath());
        yield (new SqlAlterIndex())->setPath($this->getPath());
    }

}
