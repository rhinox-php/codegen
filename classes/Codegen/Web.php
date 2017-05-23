<?php
namespace Rhino\Codegen\Codegen;

class Web extends \Rhino\Codegen\Codegen {
    public function __construct() {
        parent::__construct();
        $this->npm = new Web\Npm($this);
        $this->bower = new Web\Bower($this);
        $this->gulp = new Web\Gulp($this);
    }

    public function generate() {
        parent::generate();
        $this->npm->generate();
    }
}
