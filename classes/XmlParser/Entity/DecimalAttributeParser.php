<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class DecimalAttributeParser extends \Rhino\Codegen\XmlParser\NodeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\DecimalAttribute();
        $attribute->setName((string) $node['name']);
        $entity->addAttribute($attribute);
    }
}
