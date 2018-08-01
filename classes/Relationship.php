<?php
namespace Rhino\Codegen;

class Relationship
{
    use StandardNames;
    use Inflector;

    protected $from;
    protected $to;

    public function getFrom(): Entity
    {
        return $this->from;
    }

    public function setFrom(Entity $from)
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): Entity
    {
        return $this->to;
    }

    public function setTo(Entity $to)
    {
        $this->to = $to;
        return $this;
    }

    public function is(array $types): bool
    {
        foreach ($types as $type) {
            $type = 'Rhino\\Codegen\\Relationship\\' . $type;
            if ($this instanceof $type) {
                return true;
            }
        }
        return false;
    }
}
