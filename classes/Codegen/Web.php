<?php
namespace Rhino\Codegen\Codegen;

class Web extends \Rhino\Codegen\Codegen
{
    public $gitIgnore;
    public $composer;
    public $npm;
    public $bower;
    public $gulp;
    public $router;

    public function __construct()
    {
        parent::__construct();
        $this->gitIgnore = new GitIgnore($this);
        $this->composer = new Web\Composer($this);
        $this->npm = new Web\Npm($this);
        $this->bower = new Web\Bower($this);
        $this->gulp = new Web\Gulp($this);
        $this->env = new Web\Env($this);
        $this->router = new Web\Router($this);
    }

    public function generate()
    {
        parent::generate();
        $this->gitIgnore->generate();
        $this->composer->generate();
        $this->npm->generate();
        $this->env->generate();
        $this->router->generate();
    }
}
