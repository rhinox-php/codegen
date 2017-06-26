<?php
namespace Rhino\Codegen\Template\Generic;

class ModelInitial extends Model {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/classes/model-initial', 'src/classes/Model/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
