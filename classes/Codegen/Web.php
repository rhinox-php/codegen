<?php
namespace Rhino\Codegen\Codegen;

class Web extends \Rhino\Codegen\Codegen {
    public $composer;
    public $npm;
    public $bower;
    public $gulp;

    public function __construct() {
        parent::__construct();
        $this->composer = new Web\Composer($this);
        $this->npm = new Web\Npm($this);
        $this->bower = new Web\Bower($this);
        $this->gulp = new Web\Gulp($this);
    }

    public function generate() {
        parent::generate();
        $this->composer->generate();
        $this->npm->generate();
    }
}