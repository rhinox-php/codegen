<?php
namespace Rhino\Codegen\Template\Generic;

class Bower extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('bower', 'bower.json');
    }
}
