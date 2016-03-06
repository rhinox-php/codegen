<?php
namespace Rhino\Codegen\Relationship;

use Rhino\Codegen\Entity;
use Rhino\Codegen\Relationship;

class HasMany extends Relationship {

    protected $from;
    protected $to;
    protected $name;
    
    public function getPropertyName() {
        return $this->camelize($this->getName(), true);
    }
    
    public function getClassName() {
        return $this->camelize($this->getName());
    }
    
    public function getFrom(): Entity {
        return $this->from;
    }

    public function setFrom(Entity $from) {
        $this->from = $from;
        return $this;
    }

    public function getTo(): Entity {
        return $this->to;
    }

    public function setTo(Entity $to) {
        $this->to = $to;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name) {
        $this->name = $name;
        return $this;
    }

}
