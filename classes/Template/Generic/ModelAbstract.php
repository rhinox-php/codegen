<?php
namespace Rhino\Codegen\Template\Generic;

class ModelAbstract extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('classes/model-abstract', 'AbstractModel.php');
    }
}
