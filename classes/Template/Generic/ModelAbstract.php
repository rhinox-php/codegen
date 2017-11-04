<?php
namespace Rhino\Codegen\Template\Generic;

class ModelAbstract extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->renderTemplate('generic/classes/model-abstract', 'src/classes/Model/Generated/AbstractModel.php');
    }
}
