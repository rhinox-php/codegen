<?php
namespace Rhino\Codegen\Template\Generic;

class Gulp extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('gulpfile', 'gulpfile.js');
    }
}
