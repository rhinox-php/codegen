<?php
namespace Rhino\Codegen\Template\Laravel;

class ModelAbstract extends \Rhino\Codegen\Template\Laravel
{
    public function generate()
    {
        $this->renderTemplate('laravel/classes/model-abstract', 'src/classes/Model/Generated/AbstractModel.php');
    }
}
