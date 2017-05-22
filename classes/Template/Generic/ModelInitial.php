<?php
namespace Rhino\Codegen\Template\Generic;

class ModelInitial extends Model {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/model-initial', $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
