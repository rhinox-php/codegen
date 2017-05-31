<?php
namespace Rhino\Codegen\Template\Generic;

class Controller extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->renderTemplate('classes/controller-abstract', 'AbstractController.php');
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/controller-generated', $entity->getClassName() . 'Controller.php', [
                'entity' => $entity,
            ]);
        }
    }
}
