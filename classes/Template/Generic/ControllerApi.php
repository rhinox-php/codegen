<?php
namespace Rhino\Codegen\Template\Generic;

class ControllerApi extends Controller
{
    public function generate()
    {
        $this->codegen->composer->addDependency('rhinox/json-api-list', 'dev-master');
        $this->codegen->composer->addRepository([
            'type' => 'vcs',
            'url' => 'git@bitbucket.org:rhino-php/rhino-json-api-list',
        ]);
        $this->codegen->composer->addRepository([
            'type' => 'vcs',
            'url' => 'git@bitbucket.org:rhino-php/rhino-input-data',
        ]);

        $this->renderTemplate('generic/classes/controller-api-abstract', 'src/classes/Controller/Api/Generated/AbstractController.php');
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('generic/classes/controller-api', 'src/classes/Controller/Api/Generated/' . $entity->class . 'ApiController.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function iterateRoutes()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            yield ['get', '/api/v1/' . $entity->route . '/index', $this->getNamespace('controller-api-implemented') . '\\' . $entity->class . 'ApiController', 'index'];
            yield ['get', '/api/v1/' . $entity->route . '/get/{id}', $this->getNamespace('controller-api-implemented') . '\\' . $entity->class . 'ApiController', 'get'];
            yield ['post', '/api/v1/' . $entity->route . '/create', $this->getNamespace('controller-api-implemented') . '\\' . $entity->class . 'ApiController', 'create'];
            yield ['post', '/api/v1/' . $entity->route . '/update/{id}', $this->getNamespace('controller-api-implemented') . '\\' . $entity->class . 'ApiController', 'update'];
            yield ['post', '/api/v1/' . $entity->route . '/delete/{id}', $this->getNamespace('controller-api-implemented') . '\\' . $entity->class . 'ApiController', 'delete'];
        }
    }
}
