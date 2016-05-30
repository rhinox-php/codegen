<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class DateAttributeParser extends \Rhino\Codegen\XmlParser\NodeParser {
    public function parse(\SimpleXMLElement $node) {
        $attribute = new Attribute\DateAttribute();
        $attribute->setName((string) $node['name']);
        $entity->addAttribute($attribute);
    }
}
