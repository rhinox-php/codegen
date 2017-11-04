<?php
namespace Rhino\Codegen\Template\Laravel;

class ModelGenerated extends \Rhino\Codegen\Template\Laravel
{
    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('laravel/classes/model-generated', 'src/classes/Model/Generated/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
