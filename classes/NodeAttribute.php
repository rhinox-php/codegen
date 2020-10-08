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

    public function bool(?bool $default = false): ?bool
    {
        $value = $this->string();
        if ($value === 'true') {
            return true;
        } elseif ($value === 'false') {
            return false;
        }
        return $default;
    }

    public function int(int $default = 0): int
    {
        $value = $this->string();
        if (!preg_match('/^[0-9]+$/', $value)) {
            return $default;
        }
        return (int) $value;
    }

    public function __toString()
    {
        return $this->string();
    }

    public function string(): string
    {
        return (string) $this->value;
    }

    public function getValue()
    {
        return $this->value;
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
        $result = str_replace(['(', ')'], '', $result);
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result);
        $result = $inflector->underscore($result);
        $result = preg_replace('/([0-9]+)/', '_$1', $result);
        $result = preg_replace('/[^a-z0-9-_]+/i', '_', $result);
        $result = trim($result, '_');
        return new static($this->name, $result);
    }

    public function hyphenate(): self
    {
        $inflector = $this->getInflector();
        $result = $this->string();
        $result = str_replace(['(', ')'], '', $result);
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result);
        $result = $inflector->hyphenate($result);
        $result = preg_replace('/[^a-z0-9-_]+/i', '-', $result);
        $result = trim($result, '-');
        return new static($this->name, $result);
    }

    public function camelize(bool $lowercaseFirstLetter = false): self
    {
        $inflector = $this->getInflector();
        $result = $this->string();
        $result = str_replace(['(', ')'], '', $result);
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result, $lowercaseFirstLetter);
        $result = preg_replace('/[^a-z0-9-_]+/i', '', $result);
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
        $result = preg_replace('/\bid\b/i', 'ID', $result);
        $result = preg_replace('/\buuid\b/i', 'UUID', $result);
        return new static($this->name, $result);
    }
}
