<?php
namespace Rhino\Codegen;

class Codegen {
    use Inflector;
    
    protected $namespace;
    protected $entities = [];
    protected $relationships = [];

    public function generate() {
        $codegen = $this;
        $this->renderTemplate('pdo/include', __DIR__ . '/../example/include.php');
        $this->renderTemplate('pdo/router', __DIR__ . '/../example/router.php');
        $this->renderTemplate('pdo/bin/server', __DIR__ . '/../example/bin/server.bat');
        $this->renderTemplate('pdo/composer', __DIR__ . '/../example/composer.json');
        $this->renderTemplate('pdo/environment/local', __DIR__ . '/../example/environment/local.php');
        $this->renderTemplate('pdo/public/index', __DIR__ . '/../example/public/index.php', [
            'entities' => $this->entities,
        ]);
        foreach ($this->entities as $entity) {
            $this->renderTemplate('pdo/classes/model', __DIR__ . '/../example/classes/Model/' . $entity->getName() . '.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/classes/controller', __DIR__ . '/../example/classes/Controller/' . $entity->getName() . 'Controller.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/views/index', __DIR__ . '/../example/views/' . $entity->getFileName() . '/index.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/views/form', __DIR__ . '/../example/views/' . $entity->getFileName() . '/form.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/classes/application', __DIR__ . '/../example/classes/Application.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/sql/full/create-table', __DIR__ . '/../example/sql/full/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);

            foreach ($entity->getRelationships() as $relationship) {
                $this->renderTemplate('pdo/sql/full/create-relationship-table', __DIR__ . '/../example/sql/full/' . $entity->getTableName() . '_' . $relationship->getTo()->getTableName() . '.sql', [
                    'entity' => $entity,
                    'relationship' => $relationship,
                ]);
            }
        }
    }

    protected function renderTemplate(string $template, string $outputFile, array $data = []) {
        $codegen = $this;
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../templates/' . $template . '.php';
        $output = ob_get_clean();
        if (!file_exists(dirname($outputFile))) {
            mkdir(dirname($outputFile), 0777, true);
        }
        file_put_contents($outputFile, $output);
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        return $this;
    }

    public function getDatabaseName() {
        return $this->getInflector()->underscore($this->getNamespace());
    }

    public function getEntities() {
        return $this->entities;
    }

    public function setEntities($entities) {
        $this->entities = $entities;
        return $this;
    }

    public function addEntity($entity) {
        $this->entities[] = $entity;
        return $this;
    }

    public function findEntity(string $name) {
        foreach ($this->getEntities() as $entity) {
            if ($entity->getName() === $name) {
                return $entity;
            }
        }
        throw new \Exception('Could not find entity: ' . $name);
    }

    public function getRelationships() {
        return $this->relationships;
    }

    public function setRelationships($relationships) {
        $this->relationships = $relationships;
        return $this;
    }

    public function addRelationships($relationship) {
        $this->relationships[] = $relationship;
        return $this;
    }

}
