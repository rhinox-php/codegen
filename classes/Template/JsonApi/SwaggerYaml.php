<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerYaml extends \Rhino\Codegen\Template\Template {
    protected $name = 'json-api';
    protected $path = null;
    
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('docs/swagger', $this->getPath().'/'.$entity->getFileName().'.yml', [
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
}
