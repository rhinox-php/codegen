<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerYamlInitial extends \Rhino\Codegen\Template\Template
{
    protected $jsonApi;

    public function __construct(JsonApi $jsonApi) {
        $this->jsonApi = $jsonApi;
    }

    public function generate()
    {
        $this->renderTemplate('json-api/docs/api.yml', 'docs/api.yml', [
        ]);
        $this->renderTemplate('json-api/docs/definitions.yml', 'docs/definitions.yml', [
            'entities' => $this->codegen->node->children('entity'),
        ]);
        $this->renderTemplate('json-api/docs/paths.yml', 'docs/paths.yml', [
            'entities' => $this->codegen->node->children('entity'),
        ]);
    }
}
