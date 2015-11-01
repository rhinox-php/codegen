<?php
namespace Rhino\Codegen;

class Codegen {
    
    protected $namespace;
    protected $entities = [];
    protected $relationships = [];
    
    public function generate() {
        $codegen = $this;
        foreach ($this->entities as $entity) {
            require __DIR__ . '/../templates/entity.php';
        }
    }
    
    public function getNamespace() {
        return $this->namespace;
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
