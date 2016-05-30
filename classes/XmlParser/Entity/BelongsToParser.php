<?php
namespace Rhino\Codegen\XmlParser\Entity;

class BelongsToParser extends \Rhino\Codegen\XmlParser\NodeParser {
    public function parse(\SimpleXMLElement $node) {
        $to = $this->codegen->findEntity((string) $node['entity']);

        $attribute = new Attribute\IntAttribute();
        $attribute->setName($to->getName() . ' ID');
        $entity->addAttribute($attribute);

        $relationship = new Relationship\BelongsTo();
        $relationship->setFrom($entity);
        $relationship->setTo($to);
        $relationship->setName((string) $node['name'] ?: (string) $node['entity']);
        $entity->addRelationship($relationship);
        $to->addRelationship($relationship);
    }
}
