<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerModelForm extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->codegen->router->addRoute()
                ->setHttpMethods(['get', 'post'])
                ->setUrlPath('/admin/' . $entity->route . '/create/{id}')
                ->setControllerClass($this->getNamespace('controller-implemented') . '\\' . $entity->class . '\\FormController')
                ->setControllerMethod('create');
            $this->codegen->router->addRoute()
                ->setHttpMethods(['get', 'post'])
                ->setUrlPath('/admin/' . $entity->route . '/edit/{id}')
                ->setControllerClass($this->getNamespace('controller-implemented') . '\\' . $entity->class . '\\FormController')
                ->setControllerMethod('edit');
            $this->renderTemplate('generic/classes/controller-model-form', 'src/classes/Controller/' . $entity->class . '/FormController.php', [
                'entity' => $entity,
            ]);
        }
    }
}
