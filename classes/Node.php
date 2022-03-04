<?php

namespace Rhino\Codegen;

class Node
{
    private array $names = [];
    public array $attributes = [];
    public array $children = [];
    public string $type;
    public ?string $xml;
    public string $content;

    public function __construct(\SimpleXMLElement $xmlNode, XmlParser $xmlParser)
    {
        $this->type = $xmlNode->getName();
        $this->xml = $this->toXml($xmlNode);
        $this->content = (string) $xmlNode;
        foreach ($xmlNode->attributes() as $name => $value) {
            $this->attributes[(string) $name] = (string) $value;
        }
        foreach ($xmlNode->children() as $name => $value) {
            $this->children[] = new static($value, $xmlParser);
        }
        foreach ($xmlParser->name($this) as $name => $value) {
            $this->names[$name] = (string) $value;
        }
    }

    public function __get($name)
    {
        if (!isset($this->names[$name])) {
            $name = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $name));
            if ($this->attr($name)->string() === 'true') {
                return true;
            }
            if ($this->attr($name)->string() === 'false') {
                return false;
            }
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

    public function bool($name, ?bool $default)
    {
        return $this->attr($name)->bool($default);
    }

    private function toXml(\SimpleXMLElement $xmlNode): ?string
    {
        $tag = $xmlNode->getName();
        $value = $xmlNode->__toString();
        if ('' === $value) {
            return null;
        }
        return preg_replace('!<' . $tag . '(?:[^>]*)>(.*)</' . $tag . '>!Ums', '$1', $xmlNode->asXml());
    }

    public function xml(): ?string
    {
        return $this->xml;
    }

    public function getContent(): string
    {
        return $this->content;
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

    public function hasChild(string ...$types): bool
    {
        return $this->child(...$types) ? true : false;
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

    public function find($type)
    {
        foreach ($this->children as $node) {
            if ($node->type == $type) {
                return $node;
            }
        }
        return null;
    }

    // public function find($type, $name)
    // {
    //     foreach ($this->children as $node) {
    //         if ($node->type == $type && $node->name == $name) {
    //             return $node;
    //         }
    //     }
    //     return null;
    // }
}
