<?php
namespace Rhino\Codegen;

class Codegen {
    use Inflector;
    use Logger;

    protected $namespace = null;
    protected $projectName;
    protected $entities = [];
    protected $relationships = [];
    protected $dryRun = true;
    protected $xmlParser;
    protected $templatePath;
    protected $viewPathPrefix;
    protected $classPathPrefix;
    protected $databaseName;
    protected $templates = [];
    protected $pdo = null;
    protected $path = null;
    protected $debug = false;

    public function __construct() {
    }

    public function generate() {
        $this->log('Generating templates...');
        assert(is_dir($this->getPath()), 'Codegen path not set, or does not exist: ' . $this->getPath());
        foreach ($this->getTemplates() as $template) {
            $this->debug(get_class($template));
            $template->generate();
        }
        $this->log('Generating templates complete!');
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

    public function dbReset() {
        $pdo = $this->getPdo();
        foreach ($this->iterateTemplates() as $template) {
            if ($template instanceof Template\Interfaces\DbReset) {
                foreach ($template->iterateSql() as $sql) {
                    $this->getPdo()->query($sql);
                }
            }
        }
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

    public function debug(string ...$messages) {
        // @todo inject a logger
        if (!$this->isDebug() || empty($messages)) {
            return;
        }
        echo ($this->dryRun ? '[DRY RUN] ' : '') . '[DEBUG] ' . implode(' ', $messages) . PHP_EOL;
    }

    public function log(string ...$messages) {
        // @todo inject a logger
        if (empty($messages)) {
            return;
        }
        echo ($this->dryRun ? '[DRY RUN] ' : '') . implode(' ', $messages) . PHP_EOL;
    }

    public function getProjectName(): string {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): self {
        $this->projectName = $projectName;
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

    public function iterateTemplates($templates = null) {
        $templates = $templates ?: $this->getTemplates();
        foreach ($templates as $template) {
            if ($template instanceof Template\Aggregate) {
                yield from $this->iterateTemplates($template->iterateTemplates());
            } else {
                yield $template;
            }
        }
    }

    public function addTemplate(Template\Template $template) {
        $this->templates[] = $template;
        $template->setCodegen($this);
        $template->setPath($this->getPath());
        $template->setNamespace($this->getNamespace());
        return $template;
    }

    public function getPath(string $path = ''): string {
        return $this->path . '/' . $path;
    }

    public function setPath(string $path): self {
        assert(is_dir($path), 'Expected path to be a valid directory: ' . $path);
        $this->path = $path;
        return $this;
    }

    public function getFile(string $file): string {
        $file = $this->getPath($file);
        if (file_exists($file)) {
            $file = realpath($file);
        } elseif (is_dir(dirname($file))) {
            $file = realpath(dirname($file)) . DIRECTORY_SEPARATOR . basename($file);
        }
        return $file;
    }

    public function writeFile(string $file, string $content) {
        if (is_file($file)) {
            if (md5($content) === md5_file($file)) {
                $this->debug('No changes to', $file);
                return;
            }
        }
        $this->log(is_file($file) ? 'Overwriting' : 'Writing', strlen($content), 'bytes to', $file);
        if (!$this->isDryRun()) {
            file_put_contents($file, $content);
        }
    }

    public function isDebug(): bool {
        return $this->debug;
    }

    public function setDebug(bool $debug): self {
        $this->debug = $debug;
        return $this;
    }

    public function getNamespace(): string {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self {
        $this->namespace = $namespace;
        return $this;
    }

}
