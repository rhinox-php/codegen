<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerApiInitial extends Controller
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/classes/controller-api-initial', 'src/classes/Controller/Api/' . $entity->class . 'ApiController.php', [
                'entity' => $entity,
            ]);
        }
    }
}
