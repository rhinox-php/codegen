<?php
namespace Rhino\Codegen\Template\JsonApi;

class Swagger extends \Rhino\Codegen\Template\Aggregate {
    public function aggregate() {
        yield (new SwaggerYaml())->setPath($this->getPath());
        yield (new SwaggerYamlInitial())->setPath($this->getPath());
        yield (new SwaggerGenerateDocs())->setPath($this->getPath());
    }
}
