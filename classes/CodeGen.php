<?php
namespace Rhino\Codegen;

class Codegen {
    use Inflector;

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
    protected $port = 3000;
    protected $template;

    public function __construct(XmlParser $xmlParser) {
        $this->xmlParser = $xmlParser;
    }

    public function generate($path) {
        switch ($this->template) {
            case 'pdo': {
                (new Template\Pdo($this, $path))->generate();
                break;
            }
            case 'laravel': {
                (new Template\Laravel($this, $path))->generate();
                break;
            }
        }
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

    public function getPort(): int {
        return $this->port;
    }

    public function setPort(int $port) {
        $this->port = $port;
        return $this;
    }

    public function getTemplate(): string {
        return $this->template;
    }

    public function setTemplate(string $template) {
        $this->template = $template;
        return $this;
    }
}
