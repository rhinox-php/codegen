<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class BoolAttributeParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\BoolAttribute();
        $attribute->setName((string) $node['name']);
        $attribute->setJsonSerialize((string) $node['json-serialize'] !== false);
        $this->entity->addAttribute($attribute);
    }
}
