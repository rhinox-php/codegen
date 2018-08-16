<?php
namespace Rhino\Codegen\Template\Sync;

class Controller extends \Rhino\Codegen\Template\Template
{
    public function __construct() {
        $this->setDefaultNamespace('controller-sync', 'Controller\Sync');
    }

    public function generate()
    {
        $this->renderTemplate('sync/controller', 'src/classes/Controller/Sync/SyncController.php', [
            'entities' => $this->codegen->node->children('entity'),
        ]);
    }

    public function iterateRoutes()
    {
        yield ['get', '/api/v1/sync', $this->getNamespace('controller-sync') . '\\SyncController', 'sync'];
    }
}
