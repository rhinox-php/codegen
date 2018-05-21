<?php
namespace Rhino\Codegen\XmlParser;

class EntityParser extends AggregateParser
{
    private $entity;

    public function __construct()
    {
        $this->addParser('string-attribute', new Entity\StringAttributeParser());
        $this->addParser('int-attribute', new Entity\IntAttributeParser());
        $this->addParser('decimal-attribute', new Entity\DecimalAttributeParser());
        $this->addParser('text-attribute', new Entity\TextAttributeParser());
        $this->addParser('date-attribute', new Entity\DateAttributeParser());
        $this->addParser('date-time-attribute', new Entity\DateTimeAttributeParser());
        $this->addParser('bool-attribute', new Entity\BoolAttributeParser());
        $this->addParser('object-attribute', new Entity\ObjectAttributeParser());
        $this->addParser('array-attribute', new Entity\ArrayAttributeParser());
        $this->addParser('has-one', new Entity\HasOneParser());
        $this->addParser('has-many', new Entity\HasManyParser());
        $this->addParser('belongs-to', new Entity\BelongsToParser());
        $this->addParser('reference', new Entity\ReferenceParser());
        $this->addParser('authentication', new Entity\AuthenticationParser());
    }

    public function preparse(\SimpleXMLElement $node): void
    {
        $this->codegen->debug('Parsing entity', (string) $node['name']);

        $entity = new \Rhino\Codegen\Entity();
        $entity->setType($node->getName());
        $entity->setName((string) $node['name']);
        $entity->setPluralName((string) $node['plural-name']);
        $this->codegen->addEntity($entity);
    }

    public function parseNode(\SimpleXMLElement $node): void
    {
        $this->entity = $this->codegen->findEntity((string) $node['name']);
    }

    public function parseChildNode(\SimpleXMLElement $node, NodeParser $childParser): void
    {
        $childParser->setEntity($this->entity);
    }
}
