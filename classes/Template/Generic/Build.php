<?php
namespace Rhino\Codegen\Template\Generic;

class Build extends \Rhino\Codegen\Template\Aggregate {

    public function aggregate() {
        yield Gulp::class;
    }

}
