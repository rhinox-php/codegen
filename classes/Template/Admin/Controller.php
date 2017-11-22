<?php
namespace Rhino\Codegen\Template\Admin;

class Controller extends \Rhino\Codegen\Template\Admin
{
    public function generate()
    {
        $this->codegen->composer->addDependency('rhinox/json-data-table', 'dev-master');
        $this->codegen->composer->addRepository([
            'type' => 'vcs',
            'url' => 'git@bitbucket.org:rhino-php/rhino-data-table',
        ]);

        $this->renderTemplate('admin/classes/controller-abstract', 'src/classes/Controller/Admin/Generated/AbstractController.php');
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('admin/classes/controller', 'src/classes/Controller/Admin/Generated/' . $entity->getClassName() . 'AdminController.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('admin/classes/controller-initial', 'src/classes/Controller/Admin/' . $entity->getClassName() . 'AdminController.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function iterateRoutes()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            yield ['get', '/admin/' . $entity->getPluralRouteName(), $this->getNamespace('controller-admin-implemented') . '\\' . $entity->getClassName() . 'AdminController', 'index'];
            yield ['get', '/admin/' . $entity->getRouteName() . '/{id}', $this->getNamespace('controller-admin-implemented') . '\\' . $entity->getClassName() . 'AdminController', 'edit'];
            yield ['post', '/admin/' . $entity->getRouteName() . '/create', $this->getNamespace('controller-admin-implemented') . '\\' . $entity->getClassName() . 'AdminController', 'create'];
            // yield ['post', '/admin/' . $entity->getRouteName() . '/delete/{id}', $this->getNamespace('controller-admin-implemented') . '\\' . $entity->getClassName() . 'AdminController', 'delete'];
        }
    }
}
