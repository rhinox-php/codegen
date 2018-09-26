<?php
namespace Rhino\Codegen;

class NodeAttribute
{
    use Inflector;

    private $name;
    private $value;

    public function __construct($name = null, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function bool($default)
    {
        $value = $this->string();
        if ($value === 'true') {
            return true;
        } elseif ($value === 'false') {
            return false;
        }
        return $default;
    }

    public function __toString() {
        return $this->string();
    }

    public function string()
    {
        return (string) $this->value;
    }

    public function getInflector(): \ICanBoogie\Inflector
    {
        if (!$this->inflector) {
            $this->inflector = \ICanBoogie\Inflector::get(\ICanBoogie\Inflector::DEFAULT_LOCALE);
        }
        return $this->inflector;
    }

    public function setInflector(\ICanBoogie\Inflector $inflector)
    {
        $this->inflector = $inflector;
        return $this;
    }

    public function underscore(): self
    {
        $inflector = $this->getInflector();
        $result = $this->string();
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result);
        $result = $inflector->underscore($result);
        $result = preg_replace('/([0-9]+)/', '_$1', $result);
        $result = preg_replace('/[^a-z0-9-_]/i', '', $result);
        return new static($this->name, $result);
    }

    public function hyphenate(): self
    {
        $inflector = $this->getInflector();
        $result = $this->string();
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result);
        $result = $inflector->hyphenate($result);
        $result = preg_replace('/[^a-z0-9-_]/i', '', $result);
        return new static($this->name, $result);
    }

    public function camelize(bool $lowercaseFirstLetter = false): self
    {
        $inflector = $this->getInflector();
        $result = $this->string();
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result, $lowercaseFirstLetter);
        $result = preg_replace('/[^a-z0-9-_]/i', '', $result);
        return new static($this->name, $result);
    }

    public function pluralize(): self
    {
        $inflector = $this->getInflector();
        $result = $this->string();
        $result = $inflector->pluralize($result);
        return new static($this->name, $result);
    }

    public function humanize(): self
    {
        $inflector = $this->getInflector();
        $result = $this->string();
        $result = $inflector->underscore($result);
        $result = $inflector->humanize($result);
        return new static($this->name, $result);
    }
}
