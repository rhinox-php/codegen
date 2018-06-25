<?php
namespace Rhino\Codegen;

class Relationship
{
    use Inflector;

    protected $from;
    protected $to;
    protected $name;
    protected $pluralName;

    public function getPropertyName()
    {
        return $this->camelize($this->getName(), true);
    }

    public function getPluralPropertyName()
    {
        return $this->camelize($this->getPluralName(), true);
    }

    public function getClassName()
    {
        return $this->camelize($this->getName());
    }

    public function getPluralClassName()
    {
        return $this->camelize($this->getPluralName());
    }

    public function getMethodName(): string
    {
        return $this->camelize($this->getName());
    }

    public function getPluralMethodName()
    {
        return $this->camelize($this->getPluralName());
    }

    public function getColumnName(): string
    {
        return $this->underscore($this->getName());
    }

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function getPluralName()
    {
        return $this->pluralName ?: $this->pluralize($this->getName());
    }

    public function setPluralName($pluralName)
    {
        $this->pluralName = $pluralName;
        return $this;
    }
}
