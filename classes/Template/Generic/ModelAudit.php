<?php
namespace Rhino\Codegen\Template\Generic;

class ModelAudit extends \Rhino\Codegen\Template\Generic
{
    public function __construct() {
        $this->setDefaultNamespace('model-audit', 'Model\Generated\Audit');
    }

    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/classes/model-audit', 'src/classes/Model/Generated/Audit/' . $entity->class . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
