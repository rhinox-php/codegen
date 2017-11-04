<?php
namespace Rhino\Codegen\Template\Generic;

class ModelSerializer extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/classes/model-serializer', 'src/classes/Model/Serializer/' . $entity->getClassName() . 'Serializer.php', [
                'entity' => $entity,
            ]);
        }
    }
}
