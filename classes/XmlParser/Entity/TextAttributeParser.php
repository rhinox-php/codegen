<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class TextAttributeParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\TextAttribute();
        $attribute->setName((string) $node['name']);
        $this->entity->addAttribute($attribute);
    }
}
