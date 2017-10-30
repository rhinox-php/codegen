<?php
namespace Rhino\Codegen\Template\Generic;

class BinServer extends \Rhino\Codegen\Template\Generic {

    protected $port = 3000;

    public function generate() {
        $this->renderTemplate('generic/bin/server.bat', 'bin/server.bat');
        $this->renderTemplate('generic/bin/server.sh', 'bin/server.sh');
        $this->renderTemplate('generic/bin/router', 'bin/router.php');
        $this->renderTemplate('generic/public/index', 'public/index.php', [
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
