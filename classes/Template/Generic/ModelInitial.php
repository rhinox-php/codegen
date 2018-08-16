<?php
namespace Rhino\Codegen\Template\Generic;

class ModelInitial extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/classes/model-initial', 'src/classes/Model/' . $entity->class . '.php', [
                'entity' => $entity,
            ], false);
        }
    }
}
