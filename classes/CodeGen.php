<?php
namespace Rhino\Codegen;

class Codegen {
    use Inflector;
    
    protected $namespace;
    protected $entities = [];
    protected $relationships = [];
    protected $dryRun = true;

    public function generate(string $path) {
        if (!is_dir($path)) {
            throw new \Exception('Expected path to be a valid directory: ' . $path);
        }

        $this->renderTemplate('pdo/include', $path . '/include.php');
        $this->renderTemplate('pdo/router', $path . '/router.php');
        $this->renderTemplate('pdo/bin/server', $path . '/bin/server.bat');
        $this->renderTemplate('pdo/composer', $path . '/composer.json');
        $this->renderTemplate('pdo/environment/local', $path . '/environment/local.php');
        $this->renderTemplate('pdo/sql/create-database', $path . '/sql/create-database.sql');
        $this->renderTemplate('pdo/views/home', $path . '/views/home.php');
        $this->renderTemplate('pdo/views/layouts/default', $path . '/views/layouts/default.php');
        $this->renderTemplate('pdo/classes/home-controller', $path . '/classes/Controller/HomeController.php');
        $this->renderTemplate('pdo/public/index', $path . '/public/index.php', [
            'entities' => $this->entities,
        ]);
        foreach ($this->entities as $entity) {
            $this->renderTemplate('pdo/classes/model', $path . '/classes/Model/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/classes/controller', $path . '/classes/Controller/' . $entity->getClassName() . 'Controller.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/views/model/index', $path . '/views/' . $entity->getFileName() . '/index.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/views/model/form', $path . '/views/' . $entity->getFileName() . '/form.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/classes/application', $path . '/classes/Application.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('pdo/sql/full/create-table', $path . '/sql/full/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);

            foreach ($entity->getRelationships() as $relationship) {
                $this->renderTemplate('pdo/sql/full/create-relationship-table', $path . '/sql/full/' . $entity->getTableName() . '_' . $relationship->getTo()->getTableName() . '.sql', [
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
        $directory = dirname($outputFile);
        if (!file_exists($directory)) {
            $this->log('Creating directory ' . $directory);
            if (!$this->dryRun) {
                mkdir($directory, 0755, true);
            }
        }

        if (is_file($outputFile) && md5($output) == md5_file($outputFile)) {
            return;
        }

        $this->log((file_exists($outputFile) ? 'Overwriting' : 'Creating') . ' ' . $outputFile . ' from template ' . $template);
        if (!$this->dryRun) {
            file_put_contents($outputFile, $output);
        }
    }

    protected function log($message) {
        // @todo inject a logger
        echo ($this->dryRun ? '[DRY RUN] ' : '') . $message . PHP_EOL;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        return $this;
    }

    public function getDatabaseName() {
        $databaseName = str_replace('\\', '_', $this->getNamespace());
        $databaseName = $this->getInflector()->underscore($databaseName);
        return $databaseName;
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

    public function isDryRun() {
        return $this->dryRun;
    }

    public function setDryRun($dryRun) {
        $this->dryRun = $dryRun;
        return $this;
    }

}
