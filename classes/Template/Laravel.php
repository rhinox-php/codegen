<?php
namespace Rhino\Codegen\Template;

class Laravel extends Template {
    
    protected $name = 'laravel';
    protected $generatedModelPath = 'app/Models/Generated';
    protected $modelPath = 'app/Models';
    protected $fullDatabasePath = 'database/full';

    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/generated-model', $this->path . '/' . $this->generatedModelPath . '/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('classes/model', $this->path . '/' . $this->modelPath . '/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ], false);
            $this->renderTemplate('database/full', $this->path . '/' . $this->fullDatabasePath . '/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }

}
