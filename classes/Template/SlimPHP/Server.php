<?php
namespace Rhino\Codegen\Template\SlimPHP;

use Rhino\Codegen\Template\SlimPHP;

class Server extends SlimPHP {

    protected $path = 'bin';
    protected $port = 3000;

    public function generate() {
        $this->renderTemplate('bin/server', 'server.bat', [
            'port' => $this->getPort(),
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
