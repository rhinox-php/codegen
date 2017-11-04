<?php
namespace Rhino\Codegen\Template\Generic;

class ModelTest extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/tests/model', 'tests/Model/' . $entity->getClassName() . 'Test.php', [
                'entity' => $entity,
            ]);
        }
    }
}
