<?php
namespace Rhino\Codegen\Template\SlimPHP;

use Rhino\Codegen\Template\SlimPHP;

class Controller extends SlimPHP {

    protected $template = 'classes/controllers/controller-generated';

    public function generate() {
        // Render abstract controllers
        $this->renderTemplate('classes/controllers/controller-abstract', 'Controller.php');
        $this->renderTemplate('classes/controllers/controller-abstract-entity', 'EntityController.php');

        // Render entity controllers
        foreach ($this->getCodegen()->getEntities() as $entity) {
            $this->renderTemplate($this->getTemplate(), $entity->getClassName() . 'Controller.php', [
                'entity' => $entity,
            ]);
        }
    }

}
