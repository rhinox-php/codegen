<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Relationship;

class HasManyParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $to = $this->codegen->findEntity((string) $node['entity']);

        // $attribute = new Attribute\IntAttribute();
        // $attribute->setName($this->entity->getName() . ' ID');
        // $to->addAttribute($attribute);

        $relationship = new Relationship\HasMany();
        $relationship->setFrom($this->entity);
        $relationship->setTo($to);
        $relationship->setName((string) $node['name'] ?: $to->getName());
        $this->entity->addRelationship($relationship);
        $to->addRelationship($relationship);
    }
}
