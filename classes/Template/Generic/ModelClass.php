<?php
namespace Rhino\Codegen\Template\Generic;

class ModelClass extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/classes/model-class', 'src/classes/Model/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
