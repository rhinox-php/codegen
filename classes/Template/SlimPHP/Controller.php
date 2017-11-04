<?php
namespace Rhino\Codegen\Template\SlimPhp;

use Rhino\Codegen\Template\SlimPhp;

class Controller extends SlimPhp
{
    public function generate()
    {
        $this->codegen->composer->addRepository([
            'type' => 'vcs',
            'url' => 'git@bitbucket.org:rhino-php/rhino-input-data',
        ]);
        $this->codegen->composer->addDependency('rhinox/input-data', 'dev-master');

        $this->codegen->composer->addDependency('rhinox/json-api-list', 'dev-master');
        $this->codegen->composer->addRepository([
            'type' => 'vcs',
            'url' => 'git@bitbucket.org:rhino-php/rhino-json-api-list',
        ]);

        $this->codegen->composer->addDependency('slim/slim', '~3.8.1');

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
