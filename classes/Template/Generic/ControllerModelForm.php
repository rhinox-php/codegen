<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerModelForm extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->codegen->router->addRoute()
                ->setHttpMethods(['get', 'post'])
                ->setUrlPath('/admin/' . $entity->getRouteName() . '/create/{id}')
                ->setControllerClass($this->getNamespace('controller-implemented') . '\\' . $entity->getClassName() . '\\FormController')
                ->setControllerMethod('create');
            $this->codegen->router->addRoute()
                ->setHttpMethods(['get', 'post'])
                ->setUrlPath('/admin/' . $entity->getRouteName() . '/edit/{id}')
                ->setControllerClass($this->getNamespace('controller-implemented') . '\\' . $entity->getClassName() . '\\FormController')
                ->setControllerMethod('edit');
            $this->renderTemplate('generic/classes/controller-model-form', 'src/classes/Controller/' . $entity->getClassName() . '/FormController.php', [
                'entity' => $entity,
            ]);
        }
    }
}
