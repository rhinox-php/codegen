<?php
namespace Rhino\Codegen\Template\Generic;

class ModelGenerated extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->codegen->composer->addRepository([
            'type' => 'vcs',
            'url' => 'git@bitbucket.org:rhino-php/rhino-data-table',
        ]);
        $this->codegen->composer->addDependency('rhinox/data-table', 'dev-master');
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/classes/model-generated', 'src/classes/Model/Generated/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }
}
