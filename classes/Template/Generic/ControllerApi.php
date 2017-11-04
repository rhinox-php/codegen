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
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/classes/controller-api', 'src/classes/Controller/Api/Generated/' . $entity->getClassName() . 'ApiController.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function iterateRoutes()
    {
        foreach ($this->codegen->getEntities() as $entity) {
            yield ['get', '/api/v1/' . $entity->getRouteName() . '/index', $this->getNamespace('controller-api-implemented') . '\\' . $entity->getClassName() . 'ApiController', 'index'];
            yield ['get', '/api/v1/' . $entity->getRouteName() . '/get/{id}', $this->getNamespace('controller-api-implemented') . '\\' . $entity->getClassName() . 'ApiController', 'get'];
            yield ['post', '/api/v1/' . $entity->getRouteName() . '/create', $this->getNamespace('controller-api-implemented') . '\\' . $entity->getClassName() . 'ApiController', 'create'];
            yield ['post', '/api/v1/' . $entity->getRouteName() . '/update/{id}', $this->getNamespace('controller-api-implemented') . '\\' . $entity->getClassName() . 'ApiController', 'update'];
            yield ['post', '/api/v1/' . $entity->getRouteName() . '/delete/{id}', $this->getNamespace('controller-api-implemented') . '\\' . $entity->getClassName() . 'ApiController', 'delete'];
        }
    }
}
