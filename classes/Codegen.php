<?php

namespace Rhino\Codegen;

use BluePsyduck\SymfonyProcessManager\ProcessManager;
use Symfony\Component\Process\Process;

class Codegen
{
    use Inflector;
    use Logger;

    const OUTPUT_LEVEL_NONE = 0;
    const OUTPUT_LEVEL_INFO = 1;
    const OUTPUT_LEVEL_LOG = 2;
    const OUTPUT_LEVEL_DEBUG = 3;

    const DRY_RUN = 1;
    const DRY_RUN_INITIALIZING = 2;

    public $pdo;
    public $db;
    public $node;
    public $manifest;

    protected $namespace;
    protected $projectName;
    protected $dryRun = self::DRY_RUN_INITIALIZING;
    protected $force = false;
    protected $overwrite = false;
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
    protected $outputLevel = self::OUTPUT_LEVEL_LOG;
    protected $loggedOnce = [];
    protected $hooks = [];
    protected $mergeFileMapper = null;
    protected $manifestFile;
    protected $watchDirectories = [
        __DIR__ . '/',
        '.',
    ];
    protected $queue = [];

    public function __construct(
        private string $path,
    ) {
        assert(is_dir($path), 'Expected path to be a valid directory: ' . $path);
        $this->node = new NodeRoot();
        $this->manifest = new Manifest($this, $this->getPath('codegen-manifest.json'));
    }

    public function codegenInfo(): static
    {
        $this->info('Namespaces:');
        foreach ($this->iterateTemplates() as $template) {
            foreach ($template->getNamespaces() as $key => $namespace) {
                $this->infoOnce($key, $namespace);
            }
        }
        return $this;
    }

    public function generate()
    {
        $this->log('Generating templates...');
        assert(is_dir($this->getPath()), 'Codegen path not set, or does not exist: ' . $this->getPath());
        foreach ($this->getTemplates() as $template) {
            $this->debug(get_class($template));
            $template->generate();
        }

        $this->log('Starting queue:', count($this->queue));
        $processManager = new ProcessManager($this->getProcessorCoreCount(), 100, 10);
        $processManager->setProcessStartCallback(function (Process $process): void {
            $this->debug('Running:', $process->getCommandLine());
        });
        $processManager->setProcessFinishCallback(function (Process $process): void {
            $this->debug('Finished:', $process->getCommandLine());
            if (trim($process->getOutput())) {
                $this->log(trim($process->getOutput()) . PHP_EOL);
            }
            if (trim($process->getErrorOutput())) {
                $this->log(trim($process->getErrorOutput()) . PHP_EOL);
            }
        });

        while (count($this->queue) > 0) {
            $command = array_shift($this->queue);
            $processManager->addProcess(Process::fromShellCommandline($command));
        }
        $processManager->waitForAllProcesses();

        $this->log('Generating templates complete!');
        return $this;
    }

    private function getProcessorCoreCount()
    {
        if (PHP_OS_FAMILY == 'Windows') {
            $cores = shell_exec('echo %NUMBER_OF_PROCESSORS%');
        } else {
            $cores = shell_exec('nproc');
        }
        return (int) $cores;
    }

    public function clean()
    {
        $this->manifest->clean($this->isForce());
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function setManifestFile(string $manifestFile)
    {
        $this->manifestFile = $manifestFile;
        return $this;
    }

    public function dbMigrate(bool $write, bool $run): static
    {
        $pdo = $this->getPdo(false);
        if (!$this->db->databaseExists($this->getDatabaseName())) {
            $this->log('Database doesn\'t exist: ' . $this->getDatabaseName());
            $pdo->query("
                CREATE DATABASE `{$this->getDatabaseName()}`
                DEFAULT CHARACTER SET '{$this->getDatabaseCharset()}'
                DEFAULT COLLATE '{$this->getDatabaseCollation()}';
            ");
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
            return $this;
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
            return $this;
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
        return $this;
    }

    public function dbReset(): static
    {
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
        return $this;
    }

    public function unindent(string $string, $indentAmount = 0): string
    {
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
        $string = preg_replace('/^/m', str_repeat(' ', $indentAmount), $string);
        return $string;
    }

    public function createDirectory(string $directory, $permissions = 0755): Codegen
    {
        if (!file_exists($directory)) {
            $this->logOnce('Creating directory ' . $directory);
            if (!$this->isDryRun()) {
                mkdir($directory, $permissions, true);
            }
        }
        return $this;
    }

    public function debug(...$messages): static
    {
        if ($this->outputLevel < static::OUTPUT_LEVEL_DEBUG) {
            return $this;
        }
        // @todo inject a logger
        if (empty($messages)) {
            return $this;
        }
        $messages = array_map(function ($message) {
            if (is_array($message)) {
                return implode(' ', $message);
            }
            return $message;
        }, $messages);
        echo ($this->dryRun === static::DRY_RUN ? '[DRY RUN] ' : '') . '[DEBUG] ' . implode(' ', $messages) . PHP_EOL;
        return $this;
    }

    public function info(...$messages): static
    {
        if ($this->outputLevel < static::OUTPUT_LEVEL_INFO) {
            return $this;
        }
        // @todo inject a logger
        if (empty($messages)) {
            return $this;
        }
        $messages = array_map(function ($message) {
            if (is_array($message)) {
                return implode(' ', $message);
            }
            return $message;
        }, $messages);
        echo implode(' ', $messages) . PHP_EOL;
        return $this;
    }

    public function log(...$messages): static
    {
        if ($this->outputLevel < static::OUTPUT_LEVEL_LOG) {
            return $this;
        }
        // @todo inject a logger
        if (empty($messages)) {
            return $this;
        }
        $messages = array_map(function ($message) {
            if (is_array($message)) {
                return implode(' ', $message);
            }
            return $message;
        }, $messages);
        echo ($this->dryRun === static::DRY_RUN ? '[DRY RUN] ' : '') . implode(' ', $messages) . PHP_EOL;
        return $this;
    }

    public function infoOnce(...$messages): static
    {
        $key = md5(implode(' ', $messages));
        if (!isset($this->loggedOnce[$key])) {
            $this->loggedOnce[$key] = true;
            $this->info(...$messages);
        }
        return $this;
    }

    public function logOnce(...$messages): static
    {
        $key = md5(implode(' ', $messages));
        if (!isset($this->loggedOnce[$key])) {
            $this->loggedOnce[$key] = true;
            $this->log(...$messages);
        }
        return $this;
    }

    public function getProjectName(): string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): static
    {
        $this->projectName = $projectName;
        return $this;
    }

    public function getSchemaFile(): string
    {
        return $this->schemaFile;
    }

    public function setSchemaFile($schemaFile): static
    {
        $this->schemaFile = $schemaFile;
        return $this;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun > 0;
    }

    public function setDryRun($dryRun): static
    {
        $this->dryRun = (int) $dryRun;
        return $this;
    }

    public function isForce(): bool
    {
        return $this->force;
    }

    public function setForce(bool $force): static
    {
        $this->force = $force;
        return $this;
    }

    public function isOverwrite(): bool
    {
        return $this->overwrite;
    }

    public function setOverwrite(bool $overwrite): static
    {
        $this->overwrite = $overwrite;
        return $this;
    }

    public function isFiltered(string $string): bool
    {
        if (!$this->getFilter()) {
            return false;
        }
        if (preg_match('/' . $this->getFilter() . '/i', $string)) {
            $this->debug('Filtering', $string, 'on', '/' . $this->getFilter() . '/i');
            return false;
        }
        return true;
    }

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function setFilter(?string $filter): static
    {
        $this->filter = $filter;
        return $this;
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    public function setTemplatePath(string $templatePath): static
    {
        $this->templatePath = $templatePath;
        return $this;
    }

    public function getViewPathPrefix(): string
    {
        return $this->viewPathPrefix;
    }

    public function setViewPathPrefix(string $viewPathPrefix): static
    {
        $this->viewPathPrefix = $viewPathPrefix;
        return $this;
    }

    public function setDatabase(string $databaseDsn, string $databaseName, string $databaseUser, string $databasePassword): static
    {
        $this->databaseDsn = $databaseDsn;
        $this->databaseName = $databaseName;
        $this->databaseUser = $databaseUser;
        $this->databasePassword = $databasePassword;
        return $this;
    }

    public function getDatabaseDsn(): string
    {
        return $this->databaseDsn;
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    public function getDatabaseUser(): string
    {
        return $this->databaseUser;
    }

    public function getDatabasePassword(): string
    {
        return $this->databasePassword;
    }

    public function getDatabaseCharset(): string
    {
        return $this->databaseCharset;
    }

    public function getDatabaseCollation(): string
    {
        return $this->databaseCollation;
    }

    public function setDatabaseDsn(string $databaseDsn): static
    {
        $this->databaseDsn = $databaseDsn;
        return $this;
    }

    public function setDatabaseName(string $databaseName): static
    {
        $this->databaseName = $databaseName;
        return $this;
    }

    public function setDatabaseUser(string $databaseUser): static
    {
        $this->databaseUser = $databaseUser;
        return $this;
    }

    public function setDatabasePassword(string $databasePassword): static
    {
        $this->databasePassword = $databasePassword;
        return $this;
    }

    public function setDatabaseCharset(string $databaseCharset): static
    {
        $this->databaseCharset = $databaseCharset;
        return $this;
    }

    public function setDatabaseCollation(string $databaseCollation): static
    {
        $this->databaseCollation = $databaseCollation;
        return $this;
    }

    public function getPdo(bool $useDatabase = true): \PDO
    {
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
                $this->pdo->query("USE `{$this->getDatabaseName()}`");
            }
        }
        return $this->pdo;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function setTemplates(array $templates): static
    {
        $this->templates = $templates;
        return $this;
    }

    public function iterateTemplates($templates = null): \Generator
    {
        $templates = $templates ?: $this->getTemplates();
        foreach ($templates as $template) {
            if ($template instanceof Template\AggregateInterface) {
                yield from $this->iterateTemplates($template->iterateTemplates());
            } else {
                yield $template;
            }
        }
    }

    public function addTemplate(Template\Template $template): Template\Template
    {
        $this->templates[] = $template;
        $template->setCodegen($this);
        return $template;
    }

    public function getPath(string $path = ''): string
    {
        return $this->path . '/' . $path;
    }

    public function getFile(string $file): string
    {
        $file = $this->getPath($file);
        if (file_exists($file)) {
            $file = realpath($file);
        } elseif (is_dir(dirname($file))) {
            $file = realpath(dirname($file)) . DIRECTORY_SEPARATOR . basename($file);
        }
        return $file;
    }

    // public function writeFile(string $file, string $content, string $hash): bool
    // {
    //     assert(!!$file, new \Exception('Invalid file to write ' . $file));

    //     if (is_file($file)) {
    //         if (!$this->isForce() && !$this->isFileDifferent($file, $content)) {
    //             $this->debug('No changes to', $file);
    //             return false;
    //         }
    //         if (!$this->isForce() && !$this->isOverwrite() && filesize($file) > 0 && $this->manifest->getHash($file) && md5_file($file) !== $this->manifest->getHash($file)) {
    //             $this->log('Local modifications to file, not overwriting', $file, 'current hash:', md5_file($file) ?: 'null', 'manifest hash:', $this->manifest->getHash($file) ?: 'null');
    //             return false;
    //         }
    //     }
    //     $this->log(is_file($file) ? 'Overwriting' : 'Writing', strlen($content), 'bytes to', $file, md5($content));
    //     if (!$this->isDryRun()) {
    //         file_put_contents($file, $content);
    //         $this->manifest->addFile($file);
    //         return true;
    //     }
    //     return false;
    // }

    private function isFileDifferent(string $file, string $content): bool
    {
        if (!is_file($file)) {
            return true;
        }
        $fileContent = file_get_contents($file);
        assert($fileContent !== false, new \Exception('Could not read file ' . $file));
        $fileContent = preg_replace('/\s+/', '', $fileContent);
        $content = preg_replace('/\s+/', '', $content);
        return $fileContent != $content;
    }

    public function copyFile(string $from, string $to): bool
    {
        assert(is_file($from), new \Exception('Invalid file to copy from ' . $from));
        assert(!!$to, new \Exception('Invalid file to copy to ' . $to));

        if (is_file($to)) {
            if (md5_file($from) === md5_file($to)) {
                $this->debug('No changes to', $to);
                return false;
            }
        }
        $this->log(is_file($to) ? 'Copy overwriting' : 'Copying', $from, 'to', $to);
        if (!$this->isDryRun()) {
            copy($from, $to);
            $this->manifest->addFile($to);
            return true;
        }
        return false;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;
        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getOutputLevel(): int
    {
        return $this->outputLevel;
    }

    public function setOutputLevel(int $outputLevel): static
    {
        $this->outputLevel = $outputLevel;
        return $this;
    }

    public function hook(string $hookName, array $parameters): array
    {
        if (isset($this->hooks[$hookName])) {
            foreach ($this->hooks[$hookName] as $hook) {
                $this->debug('Running hook', $hookName, $hook::class);
                $parameters = $hook->process(...$parameters);
            }
        }
        return $parameters;
    }

    public function addHook(Hook\Hook $hook): static
    {
        $this->hooks[$hook->getHook()][] = $hook;
        return $this;
    }

    public function addHookCallback(string $hookName, callable $callback): static
    {
        $this->hooks[$hookName][] = new Hook\Callback($hookName, $callback);
        return $this;
    }

    protected function getMigrationName($name)
    {
        return date('Y_m_d_His_') . $this->underscore($name) . '.sql';
    }

    public function getMergeFileMapper(): ?callable
    {
        return $this->mergeFileMapper;
    }

    public function setMergeFileMapper(callable $mergeFileMapper): static
    {
        $this->mergeFileMapper = $mergeFileMapper;
        return $this;
    }

    public function getWatchDirectories(): array
    {
        return $this->watchDirectories;
    }

    public function setWatchDirectories(array $watchDirectories): static
    {
        $this->watchDirectories = $watchDirectories;
        return $this;
    }

    public function getCliArguments(string $command, array $options = []): string
    {
        $cliArguments = [];
        $cliArguments[] = 'codegen';
        $cliArguments[] = $command;
        $cliArguments[] = '--schema=' . escapeshellarg($this->getSchemaFile());
        if ($this->getFilter()) {
            $cliArguments[] = '--filter=' . escapeshellarg($this->getFilter());
        }
        if (!$this->isDryRun()) {
            $cliArguments[] = '--execute';
        }
        if ($this->getOutputLevel() === static::OUTPUT_LEVEL_DEBUG) {
            $cliArguments[] = '--debug';
        }
        if ($this->isForce()) {
            $cliArguments[] = '--force';
        }
        if ($this->isOverwrite()) {
            $cliArguments[] = '--overwrite';
        }
        foreach ($options as $key => $value) {
            if ($value) {
                $cliArguments[] = $key . '=' . escapeshellarg($value);
            } else {
                $cliArguments[] = $key;
            }
        }
        return implode(' ', $cliArguments);
    }

    public function queue($command): static
    {
        $this->queue[] = $command;
        return $this;
    }
}
