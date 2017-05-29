<?php
namespace Rhino\Codegen\Template\Generic;

class Server extends \Rhino\Codegen\Template\Generic {

    protected $port = 3000;

    public function generate() {
        $this->renderTemplate('bin/server', 'bin/server.bat');
        $this->renderTemplate('bin/router', 'bin/router.php');
        $this->renderTemplate('public/index', 'public/index.php', [
            'entities' => $this->codegen->getEntities(),
        ]);
    }

    public function getPort(): int {
        return $this->port;
    }

    public function setPort(int $port) {
        $this->port = $port;
        return $this;
    }

}
