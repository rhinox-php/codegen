<?php
namespace Rhino\Codegen\Template\Generic;

class ModelClass extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/classes/model-class', 'src/classes/Model/' . $entity->class . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
