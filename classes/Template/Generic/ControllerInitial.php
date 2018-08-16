<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerInitial extends Controller
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/classes/controller-initial', 'src/classes/Controller/' . $entity->class . 'Controller.php', [
                'entity' => $entity,
            ]);
        }
    }
}
