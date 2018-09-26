<?php
namespace Rhino\Codegen;

class Node
{
    private $attributes = [];
    private $names = [];
    public $children = [];
    public $type;

    public function __construct(\SimpleXMLElement $xmlNode, XmlParser $xmlParser)
    {
        $this->type = $xmlNode->getName();
        foreach ($xmlNode->attributes() as $name => $value) {
            $this->attributes[(string) $name] = $value;
        }
        foreach ($xmlNode->children() as $name => $value) {
            $this->children[] = new static($value, $xmlParser);
        }
        foreach ($xmlParser->name($this) as $name => $value) {
            $this->names[$name] = $value;
        }
    }

    public function __get($name)
    {
        if (!isset($this->names[$name])) {
            $name = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $name));
            return $this->attr($name)->string();
        }
        return $this->names[$name];
    }

    public function attr(string ...$names): NodeAttribute
    {
        foreach ($names as $name) {
            if (isset($this->attributes[$name])) {
                return new NodeAttribute($name, $this->attributes[$name]);
            }
        }
        return new NodeAttribute();
    }

    public function has(string ...$names): bool
    {
        foreach ($names as $name) {
            if (isset($this->attributes[$name])) {
                return true;
            }
        }
        return false;
    }

    public function bool($name, bool $default) {
        return $this->attr($name)->bool($default);
    }

    public function is(string ...$types): bool
    {
        foreach ($types as $type) {
            if ($this->type == $type) {
                return true;
            }
        }
        return false;
    }

    public function addChild(Node $child)
    {
        $this->children[] = $child;
        return $this;
    }

    public function get(string $type): ?Node
    {
        foreach ($this->children as $node) {
            if ($node->type == $type) {
                return $node;
            }
        }
        return null;
    }

    public function child(string ...$types): ?Node
    {
        foreach ($this->children as $node) {
            foreach ($types as $type) {
                if ($node->type == $type) {
                    return $node;
                }
            }
        }
        return null;
    }

    public function children(string ...$types): array
    {
        $result = [];
        foreach ($this->children as $node) {
            foreach ($types as $type) {
                if ($node->type == $type) {
                    $result[] = $node;
                }
            }
        }
        return $result;
    }

    public function find($type, $name) {
        foreach ($this->children as $node) {
            if ($node->type == $type && $node->name == $name) {
                return $node;
            }
        }
        return null;
    }
}