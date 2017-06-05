<?php
namespace Rhino\Codegen\Template\Generic;

class Model extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('classes/model-abstract', 'src/classes/Model/Generated/AbstractModel.php');
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/model-generated', 'src/classes/Model/Generated/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
