<?php
namespace Rhino\Codegen\Template\Generic;

class ApiTest extends \Rhino\Codegen\Template\Generic {

    public function generate() {
        $this->renderTemplate('tests/api.js', 'tests/api.js');
        $this->renderTemplate('tests/index.js', 'tests/index.js', [
            'entities' => $this->codegen->getEntities(),
        ]);

        $this->codegen->npm->addDevDependency('mocha', '^3.4.2');
        $this->codegen->npm->addDevDependency('dotenv', '^4.0.0');
        $this->codegen->npm->addDevDependency('request', '^2.81.0');

        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('tests/api/model.js', 'tests/api/' . $entity->getFileName() . '.js', [
                'entity' => $entity,
            ]);
        }
    }

}
