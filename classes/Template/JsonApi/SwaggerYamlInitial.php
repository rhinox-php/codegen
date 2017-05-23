<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerYamlInitial extends \Rhino\Codegen\Template\Template {
    protected $name = 'json-api';

    public function generate() {
        $this->renderTemplate('docs/api.yml', 'docs/api.yml');
        $this->renderTemplate('docs/definitions.yml', 'docs/definitions.yml', [
            'entities' => $this->codegen->getEntities(),
        ]);
        $this->renderTemplate('docs/paths.yml', 'docs/paths.yml');
    }
}
