<?php
namespace Rhino\Codegen;

class Entity
{
    use StandardNames;
    use Inflector;

    protected $type;
    protected $authentication = false;
    protected $attributes = [];
    protected $relationships = [];
    protected $nodes = [];

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function getAttributes(bool $sorted = false): array
    {
        if ($sorted) {
            $sortedAttributes = $this->attributes;
            uksort($sortedAttributes, function ($a, $b) {
                return strnatcasecmp($a, $b);
            });
            return $sortedAttributes;
        }
        return $this->attributes;
    }

    public function iterateAttributesByType(array $types): \Generator
    {
        foreach ($this->attributes as $attribute) {
            foreach ($types as $type) {
                $type = 'Rhino\\Codegen\\Attribute\\' . $type . 'Attribute';
                if ($attribute instanceof $type) {
                    yield $attribute;
                    continue 2;
                }
            }
        }
    }

    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[$attribute->getName()] = $attribute;
        return $this;
    }

    public function getRelationships(bool $sorted = false): array
    {
        if ($sorted) {
            $sortedRelationships = $this->relationships;
            uksort($sortedRelationships, function ($a, $b) {
                return strnatcasecmp($a, $b);
            });
            return $sortedRelationships;
        }
        return $this->relationships;
    }

    public function iterateRelationshipsByType(array $types): \Generator
    {
        foreach ($this->relationships as $relationship) {
            if ($relationship->getFrom() != $this) {
                continue;
            }
            foreach ($types as $type) {
                $type = 'Rhino\\Codegen\\Relationship\\' . $type;
                if ($relationship instanceof $type) {
                    yield $relationship;
                    continue 2;
                }
            }
        }
    }

    public function hasRelationshipsByType(array $types): bool
    {
        foreach ($this->iterateRelationshipsByType($types) as $relationship) {
            return true;
        }
        return false;
    }

    public function addRelationship(Relationship $relationship)
    {
        $this->relationships[$relationship->getName()] = $relationship;
        return $this;
    }

    public function getCodegen(): Codegen
    {
        return $this->codegen;
    }

    public function setCodegen(Codegen $codegen)
    {
        $this->codegen = $codegen;
    }

    public function hasAuthentication()
    {
        return $this->authentication;
    }

    public function setAuthentication($authentication)
    {
        $this->authentication = $authentication;
        return $this;
    }

    public function addNode(Node $node) {
        $this->nodes[] = $node;
        return $this;
    }

    public function get($name) {
        return $this->nodes[$name] ?? null;
    }

    public function children() {
        return $this->nodes;
    }
}
