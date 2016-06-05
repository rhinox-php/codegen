<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class DateTimeAttributeParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\DateTimeAttribute();
        $attribute->setName((string) $node['name']);
        $this->entity->addAttribute($attribute);
    }
}
