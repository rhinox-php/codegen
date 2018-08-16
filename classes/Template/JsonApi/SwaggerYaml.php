<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerYaml extends \Rhino\Codegen\Template\Template
{
    public function generate()
    {
        $this->codegen->npm->addDevDependency('bootprint', '^1.0.0');
        $this->codegen->npm->addDevDependency('bootprint-openapi', '^1.0.1');
        $this->codegen->npm->addDevDependency('json-refs', '^3.0.0');
        $this->codegen->npm->addDevDependency('js-yaml', '^3.8.4');
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('json-api/docs/definition.yml', 'docs/definitions/' . $entity->getFileName().'.yml', [
                'entity' => $entity,
            ]);
        }
    }
}
