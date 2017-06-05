<?php
namespace Rhino\Codegen\Template\Laravel;

class Model extends \Rhino\Codegen\Template\Template {
    protected $name = 'laravel';
    protected $path = null;
    protected $namespace = null;
    protected $modelTemplate = 'classes/generated-model';

    public function generate() {
        $this->renderTemplate('classes/abstract-model', $this->path . 'AbstractModel.php');
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate($this->getModelTemplate(), $this->path . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
        }
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        return $this;
    }

    public function getModelTemplate(): string {
        return $this->modelTemplate;
    }

    public function setModelTemplate(string $modelTemplate): Model {
        $this->modelTemplate = $modelTemplate;
        return $this;
    }
}
