<?php
namespace Rhino\Codegen;

class Codegen {
    use Inflector;

    protected $namespace;
    protected $projectName;
    protected $entities = [];
    protected $relationships = [];
    protected $dryRun = true;
    protected $xmlParser;
    protected $templatePath;

    public function __construct(XmlParser $xmlParser) {
        $this->xmlParser = $xmlParser;
    }

    public function generate(string $path) {
        if (!is_dir($path)) {
            throw new \Exception('Expected path to be a valid directory: ' . $path);
        }

        $this->createFiles([
            $path . '/private/scripts/application.js',
            $path . '/private/styles/layout.scss',
            $path . '/private/styles/tags.scss',
            $path . '/private/styles/mixins.scss',
            $path . '/private/styles/variables.scss',
        ]);
        $this->renderTemplate('private/styles/application', $path . '/private/styles/application.scss');
        $this->renderTemplate('bower', $path . '/bower.json');
        $this->renderTemplate('gulpfile', $path . '/gulpfile.js');
        $this->renderTemplate('package', $path . '/package.json');
        $this->renderTemplate('include', $path . '/include.php');
        $this->renderTemplate('router', $path . '/router.php');
        $this->renderTemplate('bin/server', $path . '/bin/server.bat');
        $this->renderTemplate('composer', $path . '/composer.json');
        $this->renderTemplate('environment/local', $path . '/environment/local.php');
        $this->renderTemplate('sql/create-database', $path . '/sql/create-database.sql');
        $this->renderTemplate('views/home', $path . '/views/home.php');
        $this->renderTemplate('views/layouts/default', $path . '/views/layouts/default.php');
        $this->renderTemplate('classes/home-controller', $path . '/classes/Controller/HomeController.php');
        $this->renderTemplate('public/index', $path . '/public/index.php', [
            'entities' => $this->entities,
        ]);
        foreach ($this->entities as $entity) {
            $this->renderTemplate('classes/model', $path . '/classes/Model/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('classes/controller', $path . '/classes/Controller/' . $entity->getClassName() . 'Controller.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('views/model/index', $path . '/views/' . $entity->getFileName() . '/index.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('views/model/form', $path . '/views/' . $entity->getFileName() . '/form.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('classes/application', $path . '/classes/Application.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('sql/full/create-table', $path . '/sql/full/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);

            foreach ($entity->getRelationships() as $relationship) {
                $this->renderTemplate('sql/full/create-relationship-table', $path . '/sql/full/' . $entity->getTableName() . '_' . $relationship->getTo()->getTableName() . '.sql', [
                    'entity' => $entity,
                    'relationship' => $relationship,
                ]);
            }
        }
    }

    protected function renderTemplate(string $template, string $outputFile, array $data = []) {
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

        if (is_file($outputFile) && md5($output) == md5_file($outputFile)) {
            return;
        }

        $this->log((file_exists($outputFile) ? 'Overwriting' : 'Creating') . ' ' . $outputFile . ' from template ' . $template);
        if (!$this->dryRun) {
            file_put_contents($outputFile, $output);
        }
    }

    protected function getTemplateFile($name) {
        $file = $this->templatePath . '/' . $name . '.php';
        if (is_file($file)) {
            return $file;
        }
        $file = __DIR__ . '/../templates/pdo/' . $name . '.php';
        assert(is_file($file), 'Could not find template file: ' . $name);
        return $file;
    }

    protected function createFiles(array $files) {
        foreach ($files as $file) {
            $directory = dirname($file);
            if (!file_exists($directory)) {
                $this->log('Creating directory ' . $directory);
                if (!$this->dryRun) {
                    mkdir($directory, 0755, true);
                }
            }
            if (!file_exists($file)) {
                $this->log('Creating file ' . $file);
                if (!$this->dryRun) {
                    file_put_contents($file, '');
                }
            }
        }
    }

    protected function log($message) {
        // @todo inject a logger
        echo ($this->dryRun ? '[DRY RUN] ' : '') . $message . PHP_EOL;
    }

    public function getNamespace() {
        return $this->namespace;
    }

    public function getProjectName() {
        return $this->projectName;
    }

    public function setProjectName($projectName) {
        $this->projectName = $projectName;
        return $this;
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

    public function getTemplatePath() {
        return $this->templatePath;
    }

    public function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
    }

}
