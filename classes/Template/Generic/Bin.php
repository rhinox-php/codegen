<?php
namespace Rhino\Codegen\Template\Generic;

class Bin extends \Rhino\Codegen\Template\Generic {
    use \Rhino\Codegen\Template\Aggregate;

    public function aggregate() {
        yield Server::class;
    }

}
