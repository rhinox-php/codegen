<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerYamlInitial extends \Rhino\Codegen\Template\Template {
    public function generate() {
        $this->renderTemplate('json-api/docs/api.yml', 'docs/api.yml');
        $this->renderTemplate('json-api/docs/definitions.yml', 'docs/definitions.yml', [
            'entities' => $this->codegen->getEntities(),
        ]);
        $this->renderTemplate('json-api/docs/paths.yml', 'docs/paths.yml');
    }
}
