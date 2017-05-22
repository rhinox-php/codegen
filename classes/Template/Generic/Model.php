<?php
namespace Rhino\Codegen\Template\Generic;

class Model extends \Rhino\Codegen\Template\Generic {
    protected $generatedNamespace = null;
    protected $implementedNamespace = null;

    public function generate() {
        $this->renderTemplate('classes/model-abstract', 'AbstractModel.php');
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('classes/model-generated', $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function getGeneratedNamespace() {
        return $this->generatedNamespace;
    }

    public function setGeneratedNamespace($generatedNamespace) {
        $this->generatedNamespace = $generatedNamespace;
        return $this;
    }

    public function getImplementedNamespace() {
        return $this->implementedNamespace;
    }

    public function setImplementedNamespace($implementedNamespace) {
        $this->implementedNamespace = $implementedNamespace;
        return $this;
    }
}
