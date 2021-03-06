<?php
namespace Rhino\Codegen\XmlParser\Entity;

use Rhino\Codegen\Relationship;

class HasOneParser extends AttributeParser
{
    public function parse(\SimpleXMLElement $node)
    {
        $to = $this->codegen->findEntity((string) $node['entity']);

        // $attribute = new Attribute\IntAttribute();
        // $attribute->setName(((string) $node['name'] ?: $to->getName()) . ' ID');
        // $this->entity->addAttribute($attribute);

        $relationship = new Relationship\HasOne();
        $relationship->setFrom($this->entity);
        $relationship->setTo($to);
        $relationship->setName((string) $node['name'] ?: $to->getName());
        $this->entity->addRelationship($relationship);
        $to->addRelationship($relationship);
    }
}
