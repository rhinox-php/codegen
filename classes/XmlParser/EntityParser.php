<?php
namespace Rhino\Codegen\XmlParser;

class EntityParser extends AggregateParser {
    
    public function getChildParsers(): array {
        return [
            'string-attribute' => new Entity\StringAttributeParser(),
            'int-attribute' => new Entity\IntAttributeParser(),
            'decimal-attribute' => new Entity\DecimalAttributeParser(),
            'text-attribute' => new Entity\TextAttributeParser(),
            'date-attribute' => new Entity\DateAttributeParser(),
            'date-time-attribute' => new Entity\DateTimeAttributeParser(),
            'bool-attribute' => new Entity\BoolAttributeParser(),
            'has-one' => new Entity\HasOneParser(),
            'has-many' => new Entity\HasManyParser(),
            'belongs-to' => new Entity\BelongsToParser(),
            'authentication' => new Entity\AuthenticationParser(),
        ];
    }
    
    public function parseNode(\SimpleXMLElement $node) {
        $this->entity = $this->codegen->findEntity((string) $node['name']);
    }
    
    public function parseChild(\SimpleXMLElement $node, $child) {
        return $child->setEntity($this->entity);
    }
}
