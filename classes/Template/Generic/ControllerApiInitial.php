<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerApiInitial extends Controller {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/classes/controller-api-initial', 'src/classes/Controller/Api/' . $entity->getClassName() . 'ApiController.php', [
                'entity' => $entity,
            ]);
        }
    }
}
