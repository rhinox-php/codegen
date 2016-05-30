<?php
namespace Rhino\Codegen\XmlParser\Entity;

class HasManyParser extends \Rhino\Codegen\XmlParser\NodeParser {
    public function parse(\SimpleXMLElement $node) {
        $to = $this->codegen->findEntity((string) $node['entity']);

//                    $attribute = new Attribute\IntAttribute();
//                    $attribute->setName($entity->getName() . ' ID');
//                    $to->addAttribute($attribute);

        $relationship = new Relationship\HasMany();
        $relationship->setFrom($entity);
        $relationship->setTo($to);
        $relationship->setName((string) $node['name'] ?: $to->getName());
        $entity->addRelationship($relationship);
        $to->addRelationship($relationship);
    }
}
