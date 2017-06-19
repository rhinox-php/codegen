<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerHome extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('classes/controller-home', 'src/classes/Controller/HomeController.php', []);
    }
}
