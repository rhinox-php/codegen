<?php
namespace Rhino\Codegen;

trait StandardNames
{
    protected $name;
    protected $pluralName;

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

    public function getClassName(): string
    {
        return $this->camelize($this->getName());
    }

    public function getPluralClassName(): string
    {
        return $this->camelize($this->getPluralName());
    }

    public function getTableName(): string
    {
        return $this->underscore($this->getName());
    }

    public function getPluralTableName(): string
    {
        return $this->underscore($this->getPluralName());
    }

    public function getPropertyName(): string
    {
        return $this->camelize($this->getName(), true);
    }

    public function getPluralPropertyName(): string
    {
        return $this->camelize($this->getPluralName(), true);
    }

    public function getFileName(): string
    {
        return $this->hyphenate($this->getName());
    }

    public function getPluralFileName(): string
    {
        return $this->hyphenate($this->getPluralName());
    }

    public function getRouteName(): string
    {
        return $this->hyphenate($this->getName());
    }

    public function getPluralRouteName(): string
    {
        return $this->hyphenate($this->getPluralName());
    }

    public function getLabel(): string
    {
        return $this->getName();
    }

    public function getPluralLabel(): string
    {
        return $this->humanize($this->getPluralName());
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

    public function getPluralColumnName(): string
    {
        return $this->underscore($this->getPluralName());
    }

}
