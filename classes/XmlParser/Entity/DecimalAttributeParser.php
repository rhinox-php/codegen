<?php
namespace Rhino\Codegen\XmlParser\Entity;

use Rhino\Codegen\Attribute;

class DecimalAttributeParser extends AttributeParser
{
    public function parse(\SimpleXMLElement $node)
    {
        $attribute = new Attribute\DecimalAttribute();
        $attribute->setName((string) $node['name']);
        $attribute->setPropertyName((string) $node['property']);
        $attribute->setMethodName((string) $node['method']);
        $attribute->setColumnName((string) $node['column']);
        $attribute->setJsonSerialize((string) $node['json-serialize'] !== false);
        $this->entity->addAttribute($attribute);
    }
}
