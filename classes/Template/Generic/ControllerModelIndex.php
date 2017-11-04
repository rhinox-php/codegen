<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerModelIndex extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->codegen->router->addRoute()
                ->setHttpMethods(['get', 'post'])
                ->setUrlPath('/admin/' . $entity->getPluralRouteName())
                ->setControllerClass($this->getNamespace('controller-implemented') . '\\' . $entity->getClassName() . '\\IndexController')
                ->setControllerMethod('index');
            $this->renderTemplate('generic/classes/controller-model-form', 'src/classes/Controller/' . $entity->getClassName() . '/IndexController.php', [
                'entity' => $entity,
            ]);
        }
    }
}
