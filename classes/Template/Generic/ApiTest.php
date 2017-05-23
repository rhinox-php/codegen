<?php
namespace Rhino\Codegen\Template;

class ApiTest extends Template {

    public function generate() {
        $this->renderTemplate('tests/index.js', $this->getPath('/tests/index.js'));
        $this->renderTemplate('tests/api.js', $this->getPath('/tests/api.js'));

        foreach ($this->codegen->getEntities() as $entity) {
            $this->renderTemplate('tests/api/model.js', $this->getPath('/tests/api/' . $entity->getFileName() . '.js'), [
                'entity' => $entity,
            ]);
        }
    }

}
