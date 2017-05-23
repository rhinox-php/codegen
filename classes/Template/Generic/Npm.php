<?php
namespace Rhino\Codegen\Template\Generic;

class Npm extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('package', 'package.json');
    }
}
