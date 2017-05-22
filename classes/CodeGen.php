<?php
namespace Rhino\Codegen;

class Codegen {
    use Inflector;
    use Logger;

    protected $namespace;
    protected $implementedNamespace;
    protected $projectName;
    protected $entities = [];
    protected $relationships = [];
    protected $dryRun = true;
    protected $xmlParser;
    protected $templatePath;
    protected $urlPrefix;
    protected $viewPathPrefix;
    protected $classPathPrefix;
    protected $databaseName;
    protected $templates = [];
    protected $pdo = null;
    protected $path = null;
    protected $debug = true;

    public function generate() {
        $this->log('Generating...');
        assert(is_dir($this->getPath()), 'Codegen path not set, or does not exist: ' . $this->getPath());
        foreach ($this->getTemplates() as $template) {
            $this->log(get_class($template));
            $template->generate();
        }
        $this->log('Generating complete!');
    }

    public function describe() {
        $print = function(...$args) {
            echo implode(" \t ", $args) . PHP_EOL;
        };
        foreach ($this->entities as $entity) {
            echo PHP_EOL;
            echo '-- '.$entity->getName().' -----------------------' . PHP_EOL;
            $print($entity->getClassName(), $entity->getPropertyName());
            echo 'Attributes:' . PHP_EOL;
            foreach ($entity->getAttributes() as $attribute) {
                $print('', $attribute->getName(), $attribute->getPropertyName());
            }
            echo PHP_EOL;
        }
    }

    public function tableExists(string $tableName) {
        $statement = $this->getPdo()->prepare('SHOW TABLES LIKE ?');
        $statement->execute([
            $tableName,
        ]);
        return $statement->rowCount() == 1;
    }

    public function columnExists(string $tableName, string $columnName) {
        $statement = $this->getPdo()->prepare("SHOW COLUMNS FROM $tableName LIKE ?");
        $statement->execute([
            $columnName,
        ]);
        return $statement->rowCount() == 1;
    }

    protected function getMigrationName($name) {
        return date('Y_m_d_His_') . $this->underscore($name) . '.sql';
    }

    public function migrate($path) {
        $path .= '/sql/up/';
        $this->log('Create migrations in ' . $path);
        $pdo = $this->getPdo();
        foreach ($this->entities as $entity) {
            if (!$this->tableExists($entity->getTableName())) {
                $name = $this->getMigrationName($entity->getTableName());
                $this->log('Create table migration ' . $path . $name);
                $this->renderTemplate('sql/full/create-table', $path . $name, [
                    'entity' => $entity,
                ]);
                continue;
            }
            $previous = 'id';
            foreach ($entity->getAttributes() as $attribute) {
                if (!$this->columnExists($entity->getTableName(), $attribute->getColumnName())) {
                    $this->log('Create column migration ' . $entity->getTableName() . '.' . $attribute->getColumnName());
                    $name = $this->getMigrationName($entity->getTableName() . '_' . $attribute->getColumnName());
                    $this->renderTemplate('sql/full/create-column', $path . $name, [
                        'entity' => $entity,
                        'attribute' => $attribute,
                        'previous' => $previous,
                    ]);
                }
                $previous = $attribute->getColumnName();
            }

            // @todo indexes
            // @todo triggers
        }
    }

    public function reset($entity) {
        $pdo = $this->getPdo();
        $entity = $this->findEntity($entity);
        dump($entity);
//        $this->renderTemplate('sql/full/create-table', $path . $name, [
//            'entity' => $entity,
//        ]);
    }

    public function createDirectory(string $directory, $permissions = 0755): Codegen {
        if (!file_exists($directory)) {
            $this->log('Creating directory ' . $directory);
            if (!$this->isDryRun()) {
                mkdir($directory, $permissions, true);
            }
        }
        return $this;
    }

    /*
    protected function renderTemplate($template, $outputFile, array $data = []) {
        $codegen = $this;
        $file = $this->getTemplateFile($template);
        extract(get_object_vars($this), EXTR_SKIP);
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $output = ob_get_clean();
        $directory = dirname($outputFile);
        if (!file_exists($directory)) {
            $this->log('Creating directory ' . $directory);
            if (!$this->dryRun) {
                mkdir($directory, 0755, true);
            }
        }
    }

    public function generate($path) {
        if (!is_dir($path)) {
            throw new \Exception('Expected path to be a valid directory: ' . $path);
        }

        $path .= '/';

        $this->createFiles([
            $path . '/private/scripts/application.js',
            $path . '/private/styles/layout.scss',
            $path . '/private/styles/tags.scss',
            $path . '/private/styles/mixins.scss',
            $path . '/private/styles/variables.scss',
        ]);
//        $this->renderTemplate('private/styles/application', $path . '/private/styles/application.scss');
//        $this->renderTemplate('bower', $path . '/bower.json');
//        $this->renderTemplate('gulpfile', $path . '/gulpfile.js');
//        $this->renderTemplate('package', $path . '/package.json');
//        $this->renderTemplate('include', $path . '/include.php');
        $this->renderTemplate('generated.xml', $path . '/generated.xml');
        $this->renderTemplate('bin/router', $path . '/bin/router.php');
        $this->renderTemplate('bin/server', $path . '/bin/server.bat');
//        $this->renderTemplate('composer', $path . '/composer.json');
//        $this->renderTemplate('environment/local', $path . '/environment/local.php');
        $this->renderTemplate('sql/create-database', $path . '/sql/create-database.sql');
        $this->renderTemplate('views/home', $path . $this->getViewPathPrefix() . '/home.php');
        $this->renderTemplate('views/layouts/default', $path . $this->getViewPathPrefix() . '/layouts/default.php');
//        $this->renderTemplate('classes/home-controller', $path . $this->getClassPathPrefix() . '/Controller/HomeController.php');
//        $this->renderTemplate('classes/application', $path . $this->getClassPathPrefix() . '/Application.php');
//        $this->renderTemplate('public/index', $path . '/public/index.php', [
//            'entities' => $this->entities,
//        ]);

        $this->renderTemplate('tests/index.js', $path . '/tests/index.js');

        $this->renderTemplate('tests/api.js', $path . '/tests/api.js');

        foreach ($this->entities as $entity) {
            $this->renderTemplate('classes/model', $path . $this->getClassPathPrefix() . '/Model/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('classes/controller', $path . $this->getClassPathPrefix() . '/Controller/' . $entity->getClassName() . 'Controller.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('views/model/index', $path . $this->getViewPathPrefix() . '/' . $entity->getFileName() . '/index.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('views/model/form', $path . $this->getViewPathPrefix() . '/' . $entity->getFileName() . '/form.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('sql/full/create-table', $path . '/sql/full/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);

            $this->renderTemplate('tests/api/model.js', $path . '/tests/api/' . $entity->getFileName() . '.js', [
                'entity' => $entity,
            ]);

            foreach ($entity->getRelationships() as $relationship) {
                if ($relationship instanceof Relationship\ManyToMany) {
                    $this->renderTemplate('sql/full/create-relationship-table', $path . '/sql/full/' . $entity->getTableName() . '_' . $relationship->getTo()->getTableName() . '.sql', [
                        'entity' => $entity,
                        'relationship' => $relationship,
                    ]);
                }
            }
        }
    }
    */

    public function debug($message) {
        // @todo inject a logger
        if ($this->isDebug()) {
            echo ($this->dryRun ? '[DRY RUN] ' : '') . $message . PHP_EOL;
        }
    }

    public function log($message) {
        // @todo inject a logger
        echo ($this->isDryRun() ? '[DRY RUN] ' : '') . $message . PHP_EOL;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function getProjectName() {
        return $this->projectName;
    }

    public function getImplementedNamespace() {
        return $this->implementedNamespace;
    }

    public function setImplementedNamespace($implementedNamespace) {
        $this->implementedNamespace = $implementedNamespace;
        return $this;
    }

    public function setProjectName($projectName) {
        $this->projectName = $projectName;
        return $this;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
        return $this;
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
        if (!$name) {
            throw new \Exception('Entity name missing.');
        }
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

    public function getTemplatePath() {
        return $this->templatePath;
    }

    public function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
    }

    public function getUrlPrefix() {
        return $this->urlPrefix;
    }

    public function setUrlPrefix($urlPrefix) {
        $this->urlPrefix = $urlPrefix;
        return $this;
    }

    public function getViewPathPrefix() {
        return $this->viewPathPrefix;
    }

    public function setViewPathPrefix($viewPathPrefix) {
        $this->viewPathPrefix = $viewPathPrefix;
        return $this;
    }

    public function getClassPathPrefix() {
        return $this->classPathPrefix;
    }

    public function setClassPathPrefix($classPathPrefix) {
        $this->classPathPrefix = $classPathPrefix;
        return $this;
    }

    public function getDatabaseName() {
        return $this->databaseName;
    }

    public function setDatabaseName($databaseName) {
        $this->databaseName = $databaseName;
    }

    public function getPdo(): \PDO {
        return $this->pdo;
    }

    public function setPdo(\PDO $pdo) {
        $this->pdo = $pdo;
        return $this;
    }

    public function getTemplates(): array {
        return $this->templates;
    }

    public function setTemplates(array $templates) {
        $this->templates = $templates;
        return $this;
    }

    public function addTemplate(Template\Template $template) {
        $this->templates[] = $template;
        $template->setCodegen($this);
        return $this;
    }

    public function getPath(string $path = ''): string {
        return $this->path . '/' . $path;
    }

    public function setPath(string $path): self {
        assert(is_dir($path), 'Expected path to be a valid directory: ' . $path);
        $this->path = $path;
        return $this;
    }

    public function isDebug(): bool {
        return $this->debug;
    }

    public function setDebug(bool $debug): self {
        $this->debug = $debug;
        return $this;
    }

}
