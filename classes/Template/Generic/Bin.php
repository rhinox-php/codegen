<?php
namespace Rhino\Codegen\Template\Generic;

class Bin extends \Rhino\Codegen\Template\Aggregate {

    public function aggregate() {
        yield (new Server())->setPath($this->getPath());
    }

}
