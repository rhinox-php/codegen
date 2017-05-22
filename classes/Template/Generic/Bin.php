<?php
namespace Rhino\Codegen\Template\Generic;

class Bin extends \Rhino\Codegen\Template\Aggregate {

    protected $path = null;

    public function aggregate() {
        yield (new Server())->setPath($this->getPath());
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

}
