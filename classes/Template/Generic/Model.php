<?php
namespace Rhino\Codegen\Template\Generic;

class Model extends \Rhino\Codegen\Template\Generic {
    
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/model', $this->getModelPath($entity->getClassName() . '.php'), [
                'entity' => $entity,
            ]);
            $this->renderTemplate('sql/full/create-table', $this->getPath('/sql/full/' . $entity->getTableName() . '.sql'), [
                'entity' => $entity,
            ]);
        }
    }
    
}
