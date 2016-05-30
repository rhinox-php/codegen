<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class DateTimeAttributeParser extends \Rhino\Codegen\XmlParser\NodeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\DateTimeAttribute();
        $attribute->setName((string) $node['name']);
        $entity->addAttribute($attribute);
    }
}
