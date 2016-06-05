<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class DecimalAttributeParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\DecimalAttribute();
        $attribute->setName((string) $node['name']);
        $this->entity->addAttribute($attribute);
    }
}
