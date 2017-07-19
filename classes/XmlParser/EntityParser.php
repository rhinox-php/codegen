<?php
namespace Rhino\Codegen\XmlParser;

class EntityParser extends AggregateParser {
    private $entity;
    
    public function getChildParsers(): array {
        return [
            'string-attribute' => new Entity\StringAttributeParser(),
            'int-attribute' => new Entity\IntAttributeParser(),
            'decimal-attribute' => new Entity\DecimalAttributeParser(),
            'text-attribute' => new Entity\TextAttributeParser(),
            'date-attribute' => new Entity\DateAttributeParser(),
            'date-time-attribute' => new Entity\DateTimeAttributeParser(),
            'bool-attribute' => new Entity\BoolAttributeParser(),
            'object-attribute' => new Entity\ObjectAttributeParser(),
            'array-attribute' => new Entity\ArrayAttributeParser(),
            'has-one' => new Entity\HasOneParser(),
            'has-many' => new Entity\HasManyParser(),
            'belongs-to' => new Entity\BelongsToParser(),
            'reference' => new Entity\ReferenceParser(),
            'authentication' => new Entity\AuthenticationParser(),
        ];
    }
    
    public function preparse(\SimpleXMLElement $node): void {
        $this->codegen->log('Parsing entity', (string) $node['name']);

        $entity = new \Rhino\Codegen\Entity();
        $entity->setName((string) $node['name']);
        $entity->setPluralName((string) $node['plural-name']);
        $this->codegen->addEntity($entity);
    }
    
    public function parseNode(\SimpleXMLElement $node): void {
        $this->entity = $this->codegen->findEntity((string) $node['name']);
    }
    
    public function parseChildNode(\SimpleXMLElement $node, NodeParser $childParser): void {
        $childParser->setEntity($this->entity);
    }
}
