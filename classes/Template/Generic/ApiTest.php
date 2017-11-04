<?php
namespace Rhino\Codegen\Template\Generic;

class ApiTest extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->renderTemplate('generic/tests/api.js', 'tests/api.js');
        $this->renderTemplate('generic/tests/index.js', 'tests/index.js', [
            'entities' => $this->codegen->getEntities(),
        ]);

        $this->codegen->npm->addDevDependency('mocha', '^3.4.2');
        $this->codegen->npm->addDevDependency('dotenv', '^4.0.0');
        $this->codegen->npm->addDevDependency('request', '^2.81.0');
        $this->codegen->npm->addDevDependency('faker', '^4.1.0');

        // @todo use custom port
        $this->codegen->env->add('TEST_BASE_URL', 'http://localhost:3000/api/v1/');

        $this->renderTemplate('generic/bin/test-api.sh', 'bin/test-api.sh')->setExecutable(true);

        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('generic/tests/api/model.js', 'tests/api/' . $entity->getFileName() . '.js', [
                'entity' => $entity,
            ]);
        }
    }
}
