<?php
namespace Rhino\Codegen\Template\Generic;

class ModelGenerated extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/model-generated', $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
