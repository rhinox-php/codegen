<?php
namespace Rhino\Codegen\Cli;

class Application extends \Symfony\Component\Console\Application {
    public function __construct() {
        parent::__construct();
        $this->add(new Command\DbMigrate());
        $this->add(new Command\DbReset());
        $this->add(new Command\Desc());
        $this->add(new Command\Gen());
        $this->add(new Command\Init());
        $this->add(new Command\MakeMigration());
        $this->add(new Command\Watch());
    }
}
