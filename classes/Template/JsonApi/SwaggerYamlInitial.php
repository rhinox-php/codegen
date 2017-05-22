<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerYamlInitial extends \Rhino\Codegen\Template\Template {
    protected $name = 'json-api';

    public function generate() {
        $this->renderTemplate('docs/swagger-initial', 'api.yml');
    }
}
