<?php
namespace Rhino\Codegen\XmlParser\Entity;

use Rhino\Codegen\Attribute;

class ObjectAttributeParser extends AttributeParser
{
    public function parse(\SimpleXMLElement $node)
    {
        $attribute = new Attribute\ObjectAttribute();
        $attribute->setName((string) $node['name']);
        $attribute->setPropertyName((string) $node['property']);
        $attribute->setMethodName((string) $node['method']);
        $attribute->setColumnName((string) $node['column']);
        $attribute->setJsonSerialize((string) $node['json-serialize'] !== false);
        $attribute->setClass((string) $node['class']);
        $this->entity->addAttribute($attribute);
    }
}
