<?php
namespace Rhino\Codegen\Relationship;

use Rhino\Codegen\Entity;
use Rhino\Codegen\Relationship;

class BelongsTo extends Relationship {

    protected $from;
    protected $to;

    public function getFrom(): Entity {
        return $this->from;
    }

    public function setFrom(Entity $from) {
        $this->from = $from;
    }

    public function getTo(): Entity {
        return $this->to;
    }

    public function setTo(Entity $to) {
        $this->to = $to;
    }

}
