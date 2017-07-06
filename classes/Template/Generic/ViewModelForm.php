<?php
namespace Rhino\Codegen\Template\Generic;

class ViewModelForm extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/views/model/form', 'src/views/' . $entity->getFileName() . '/form.php', [
                'entity' => $entity,
            ]);
        }
    }
}
