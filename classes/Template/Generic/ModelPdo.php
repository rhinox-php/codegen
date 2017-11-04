<?php
namespace Rhino\Codegen\Template\Generic;

class ModelPdo extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->renderTemplate('generic/classes/model-pdo', 'src/classes/Model/Generated/PdoModel.php');
    }
}
