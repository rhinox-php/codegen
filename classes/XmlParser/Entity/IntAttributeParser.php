<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class IntAttributeParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\IntAttribute();
        $attribute->setName((string) $node['name']);
        $this->entity->addAttribute($attribute);
    }
}
