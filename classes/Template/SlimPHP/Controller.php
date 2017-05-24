<?php
namespace Rhino\Codegen\Template\SlimPHP;

use Rhino\Codegen\Template\SlimPHP;

class Controller extends SlimPHP {

    public function generate() {
        // Render abstract controllers
        $this->renderTemplate('classes/controller/controller-abstract', 'src/classes/Controller/Controller.php');
        $this->renderTemplate('classes/controller/controller-abstract-entity', 'src/classes/Controller/EntityController.php');

        // Render entity controllers
        foreach ($this->getCodegen()->getEntities() as $entity) {
            $this->renderTemplate('classes/controller/controller-generated', 'src/classes/Controller/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }

}
