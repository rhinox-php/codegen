<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class TextAttributeParser extends \Rhino\Codegen\XmlParser\NodeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\TextAttribute();
        $attribute->setName((string) $node['name']);
        $entity->addAttribute($attribute);
    }
}
