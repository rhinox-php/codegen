<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class BoolAttributeParser extends \Rhino\Codegen\XmlParser\NodeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\BoolAttribute();
        $attribute->setName((string) $node['name']);
        $entity->addAttribute($attribute);
    }
}
