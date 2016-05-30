<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerYaml extends \Rhino\Codegen\Template\Generic {
    
    protected $name = 'json-api';
    
    public function generate() {
        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('docs/swagger', $this->getPath('/docs/'.$entity->getFileName().'.yml'), [
                'entity' => $entity,
            ]);
        }
    }
    
}
