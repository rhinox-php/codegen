<?php
namespace Rhino\Codegen;
use Symfony\Component\Console\Helper\Table;

class Codegen {
    use Inflector;
    use Logger;

    const OUTPUT_LEVEL_NONE = 0;
    const OUTPUT_LEVEL_INFO = 1;
    const OUTPUT_LEVEL_LOG = 2;
    const OUTPUT_LEVEL_DEBUG = 3;

    public $pdo;
    public $db;

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
    protected $databaseDsn;
    protected $databaseName;
    protected $databaseUser;
    protected $databasePassword;
    protected $databaseCharset = 'utf8mb4';
    protected $databaseCollation = 'utf8mb4_unicode_520_ci';
    protected $path;
    protected $outputLevel = self::OUTPUT_LEVEL_LOG;
    protected $loggedOnce = [];
    protected $hooks = [];

    public function __construct() {
    }

    public function validate() {
        if (!$this->namespace) {
            throw new \Exception('Codegen base namespace not set.');
        }
    }

    public function codegenInfo() {
        $this->info('Namespaces:');
        foreach ($this->iterateTemplates() as $template) {
            foreach ($template->getNamespaces() as $key => $namespace) {
                $this->infoOnce($key, $namespace);
            }
        }
    }

    public function generate() {
        $this->validate();
        $this->log('Generating templates...');
        assert(is_dir($this->getPath()), 'Codegen path not set, or does not exist: ' . $this->getPath());
        foreach ($this->getTemplates() as $template) {
            $this->debug(get_class($template));
            $template->generate();
        }
        $this->log('Generating templates complete!');
    }

    protected function getMigrationName($name) {
        return date('Y_m_d_His_') . $this->underscore($name) . '.sql';
    }

    public function dbMigrate(bool $write, bool $run) {
        $pdo = $this->getPdo(false);
        if (!$this->db->databaseExists($this->getDatabaseName())) {
            $this->log('Database doesn\'t exist: ' . $this->getDatabaseName());
            return;
        }
        $pdo->query("
            USE `{$this->getDatabaseName()}`;
        ");

        $date = date('Y_m_d_His');

        $sqlSet = [];
        foreach ($this->iterateTemplates() as $template) {
            if ($template instanceof Template\Interfaces\DatabaseMigrate) {
                $this->log('Migrating', get_class($template));
                foreach ($template->iterateDatabaseMigrateSql($pdo, $date) as $path => $sql) {
                    $sql = $this->unindent($sql);
                    $this->debug($sql);
                    if (!isset($sqlSet[$path])) {
                        $sqlSet[$path] = [];
                    }
                    $sqlSet[$path][] = $sql;
                }
            }
        }

        if (empty($sqlSet)) {
            $this->log('Nothing to migrate.');
            return;
        }

        foreach ($sqlSet as $migrationFile => $migrations) {
            $this->createDirectory(dirname($migrationFile));
            if (!$write) {
                $this->log('Not writing migration file', $migrationFile);
            } else {
                $this->writeFile($migrationFile, implode(PHP_EOL . PHP_EOL, $migrations) . PHP_EOL);
            }
        }

        if (!$run) {
            $this->log('Not running migration.');
            return;
        }

        $this->log('Running migration...');
        foreach ($sqlSet as $migrationFile => $migrations) {
            foreach ($migrations as $sql) {
                $this->debug($sql);
                if (!$this->dryRun) {
                    $this->getPdo()->query($sql);
                }
            }
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
            if ($template instanceof Template\Interfaces\DatabaseReset) {
                $this->log('Resetting', get_class($template));
                foreach ($template->iterateDatabaseResetSql() as $sql) {
                    $this->debug($sql);
                    if (!$this->dryRun) {
                        $this->getPdo()->query($sql);
                    }
                }
            }
        }
    }

    public function unindent(string $string) {
        $minWhitespace = null;
        foreach (preg_split('/\R/', $string) as $line) {
            if ($line == '') {
                continue;
            }
            if (preg_match('/^[ ]+$/', $line)) {
                continue;
            }
            if (preg_match('/^(?<whitespace>[ ]*)[^ ]/', $line, $matches)) {
                $whitespace = strlen($matches['whitespace']);
                if ($minWhitespace === null || $whitespace < $minWhitespace) {
                    $minWhitespace = $whitespace;
                }
            }
        }
        if ($minWhitespace === null) {
            return $string;
        }
        $string = preg_replace('/^[ ]{' . $minWhitespace . '}/m', '', $string);
        $string = preg_replace('/^[ ]+$/m', '', $string);
        $string = trim($string);
        return $string;
    }

    public function createDirectory(string $directory, $permissions = 0755): Codegen {
        if (!file_exists($directory)) {
            $this->logOnce('Creating directory ' . $directory);
            if (!$this->isDryRun()) {
                mkdir($directory, $permissions, true);
            }
        }
        return $this;
    }

    public function debug(...$messages) {
        if ($this->outputLevel < static::OUTPUT_LEVEL_DEBUG) {
            return;
        }
        // @todo inject a logger
        if (empty($messages)) {
            return;
        }
        $messages = array_map(function($message) {
            if (is_array($message)) {
                return implode(' ', $message);
            }
            return $message;
        }, $messages);
        echo ($this->dryRun ? '[DRY RUN] ' : '') . '[DEBUG] ' . implode(' ', $messages) . PHP_EOL;
    }

    public function info(...$messages) {
        if ($this->outputLevel < static::OUTPUT_LEVEL_INFO) {
            return;
        }
        // @todo inject a logger
        if (empty($messages)) {
            return;
        }
        $messages = array_map(function($message) {
            if (is_array($message)) {
                return implode(' ', $message);
            }
            return $message;
        }, $messages);
        echo implode(' ', $messages) . PHP_EOL;
    }

    public function log(...$messages) {
        if ($this->outputLevel < static::OUTPUT_LEVEL_LOG) {
            return;
        }
        // @todo inject a logger
        if (empty($messages)) {
            return;
        }
        $messages = array_map(function($message) {
            if (is_array($message)) {
                return implode(' ', $message);
            }
            return $message;
        }, $messages);
        echo ($this->dryRun ? '[DRY RUN] ' : '') . implode(' ', $messages) . PHP_EOL;
    }

    public function infoOnce(...$messages) {
        $key = md5(implode(' ', $messages));
        if (!isset($this->loggedOnce[$key])) {
            $this->loggedOnce[$key] = true;
            $this->info(...$messages);
        }
    }

    public function logOnce(...$messages) {
        $key = md5(implode(' ', $messages));
        if (!isset($this->loggedOnce[$key])) {
            $this->loggedOnce[$key] = true;
            $this->log(...$messages);
        }
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
            if (!$this->databaseDsn) {
                throw new \Exception('Database settings not set on Codegen.');
            }
            $this->pdo = new \PDO($this->databaseDsn, $this->databaseUser, $this->databasePassword, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => true,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->getDatabaseCharset()} COLLATE {$this->getDatabaseCollation()}",
            ]);
            $this->db = new Database\MySql($this->pdo);
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
            if ($template instanceof Template\AggregateInterface) {
                yield from $this->iterateTemplates($template->iterateTemplates());
            } else {
                yield $template;
            }
        }
    }

    public function addTemplate(Template\Template $template) {
        $this->templates[] = $template;
        $template->setCodegen($this);
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

    public function writeFile(string $file, string $content): self {
        if (!$file) {
            throw new \Exception('Invalid file to write ' . $file);
        }
        if (is_file($file)) {
            if (md5($content) === md5_file($file)) {
                $this->debug('No changes to', $file);
                return $this;
            }
        }
        $this->log(is_file($file) ? 'Overwriting' : 'Writing', strlen($content), 'bytes to', $file);
        if (!$this->isDryRun()) {
            file_put_contents($file, $content);
        }
        return $this;
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

    public function getOutputLevel(): int {
        return $this->outputLevel;
    }

    public function setOutputLevel(int $outputLevel): self {
        $this->outputLevel = $outputLevel;
        return $this;
    }

    public function hook(string $hookName, array $parameters): array {
        if (isset($this->hooks[$hookName])) {
            foreach ($this->hooks[$hookName] as $hook) {
                $parameters = $hook->process(...$parameters);
            }
        }
        return $parameters;
    }

    public function addHook(Hook\Hook $hook): self {
        $this->hooks[$hook->getHook()][] = $hook;
        return $this;
    }

    public function addHookCallback(string $hookName, callable $callback): self {
        $this->hooks[$hookName][] = new Hook\Callback($hookName, $callback);
        return $this;
    }
}
