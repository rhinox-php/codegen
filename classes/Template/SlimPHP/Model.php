<?php
namespace Rhino\Codegen\Template\SlimPHP;

use Rhino\Codegen\Template\SlimPHP;

class Model extends SlimPHP {

    protected $template = 'classes/models/model-generated';

    public function generate() {
        $this->renderTemplate('classes/models/model-abstract', 'AbstractModel.php');
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate($this->getTemplate(), 'Base' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('classes/models/model-initial', $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }

}
