<?php
namespace Rhino\Codegen\Template\Generic;

class Controller extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('classes/controller-abstract', 'src/classes/Controller/Generated/AbstractController.php');
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/controller-generated', 'src/classes/Controller/Generated/' . $entity->getClassName() . 'Controller.php', [
                'entity' => $entity,
            ]);
        }
    }
}