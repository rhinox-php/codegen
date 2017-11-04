<?php
namespace Rhino\Codegen\Template\Generic;

class ModelGenerated extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/classes/model-generated', 'src/classes/Model/Generated/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
