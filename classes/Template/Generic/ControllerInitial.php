<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerInitial extends Controller {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/controller-initial', $entity->getClassName() . 'Controller.php', [
                'entity' => $entity,
            ]);
        }
    }
}
