<?php
namespace Rhino\Codegen\Template\JsonApi;

class Swagger extends \Rhino\Codegen\Template\Aggregate {
    public function aggregate() {
        yield SwaggerYaml::class;;
        yield SwaggerYamlInitial::class;
        yield SwaggerGenerateDocs::class;
    }
}
