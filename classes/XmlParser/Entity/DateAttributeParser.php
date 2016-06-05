<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class DateAttributeParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\DateAttribute();
        $attribute->setName((string) $node['name']);
        $this->entity->addAttribute($attribute);
    }
}
