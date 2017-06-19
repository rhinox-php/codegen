<?php
namespace Rhino\Codegen;
use Symfony\Component\Console\Helper\Table;

class Codegen {
    use Inflector;
    use Logger;

    protected $namespace;
    protected $projectName;
    protected $entities = [];
    protected $relationships = [];
    protected $dryRun = true;
    protected $xmlParser;
    protected $templatePath;
    protected $viewPathPrefix;
    protected $classPathPrefix;
    protected $templates = [];
    protected $pdo;
    protected $databaseDsn;
    protected $databaseName;
    protected $databaseUser;
    protected $databasePassword;
    protected $databaseCharset = 'utf8mb4';
    protected $databaseCollation = 'utf8mb4_unicode_520_ci';
    protected $path;
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

    public function describe(\Symfony\Component\Console\Output\OutputInterface $output) {
        foreach ($this->entities as $entity) {
            $output->writeln('Entity:');
            (new Table($output))
                ->setHeaders(['Class Name', 'Property Name'])
                ->setRows([
                    [$entity->getClassName(), $entity->getPropertyName()],
                ])
                ->render();
            $output->writeln('Attributes:');
            $rows = [];
            foreach ($entity->getAttributes() as $attribute) {
                $rows[] = [$attribute->getName(), $attribute->getPropertyName(), $attribute->getType()];
            }
            (new Table($output))
                ->setHeaders(['Class Name', 'Property Name', 'Type'])
                ->setRows($rows)
                ->render();
            $output->writeln('');
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
        $pdo = $this->getPdo(false);
        $this->log('Dropping and recreating database', $this->getDatabaseName(), $this->getDatabaseCharset(), $this->getDatabaseCollation());
        if (!$this->dryRun) {
            $pdo->query("
                DROP DATABASE IF EXISTS `{$this->getDatabaseName()}`;
                CREATE DATABASE `{$this->getDatabaseName()}`
                DEFAULT CHARACTER SET '{$this->getDatabaseCharset()}'
                DEFAULT COLLATE '{$this->getDatabaseCollation()}';
                USE `{$this->getDatabaseName()}`;
            ");
        }
        foreach ($this->iterateTemplates() as $template) {
            if ($template instanceof Template\Interfaces\DbReset) {
                $this->log('Resetting', get_class($template));
                foreach ($template->iterateSql() as $sql) {
                    if (!$this->dryRun) {
                        $this->getPdo()->query($sql);
                    }
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

    public function setDatabase($databaseDsn, $databaseName, $databaseUser, $databasePassword) {
        $this->databaseDsn = $databaseDsn;
        $this->databaseName = $databaseName;
        $this->databaseUser = $databaseUser;
        $this->databasePassword = $databasePassword;
        return $this;
    }

    public function getDatabaseDsn() {
        return $this->databaseDsn;
    }

    public function getDatabaseName() {
        return $this->databaseName;
    }

    public function getDatabaseUser() {
        return $this->databaseUser;
    }

    public function getDatabasePassword() {
        return $this->databasePassword;
    }

    public function getDatabaseCharset() {
        return $this->databaseCharset;
    }

    public function getDatabaseCollation() {
        return $this->databaseCollation;
    }

    public function setDatabaseDsn($databaseDsn) {
        $this->databaseDsn = $databaseDsn;
        return $this;
    }

    public function setDatabaseName($databaseName) {
        $this->databaseName = $databaseName;
        return $this;
    }

    public function setDatabaseUser($databaseUser) {
        $this->databaseUser = $databaseUser;
        return $this;
    }

    public function setDatabasePassword($databasePassword) {
        $this->databasePassword = $databasePassword;
        return $this;
    }

    public function setDatabaseCharset($databaseCharset) {
        $this->databaseCharset = $databaseCharset;
        return $this;
    }

    public function setDatabaseCollation($databaseCollation) {
        $this->databaseCollation = $databaseCollation;
        return $this;
    }

    public function getPdo($useDatabase = true): \PDO {
        if (!$this->pdo) {
            $this->pdo = new \PDO($this->databaseDsn, $this->databaseUser, $this->databasePassword, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => true,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->getDatabaseCharset()} COLLATE {$this->getDatabaseCollation()}",
            ]);
            if ($useDatabase) {
                $this->pdo->query("USE DATABASE `{$this->getDatabaseName()}`");
            }
        }
        return $this->pdo;
    }

    public function setPdo($dsn, $user, $password) {
        $this->databaseDsn = $dsn;
        $this->databaseUser = $user;
        $this->databasePassword = $password;
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
