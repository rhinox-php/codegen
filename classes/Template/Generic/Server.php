<?php
namespace Rhino\Codegen\Template\Generic;

class Server extends \Rhino\Codegen\Template\Generic {
    
    protected $port = 3000;
    
    public function generate() {
        $this->renderTemplate('bin/server', $this->getPath('/bin/server.bat'));
    }
    
    public function getPort(): int {
        return $this->port;
    }

    public function setPort(int $port) {
        $this->port = $port;
        return $this;
    }
    
}
