<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Relationship;
use Rhino\Codegen\Attribute;

class BelongsToParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $to = $this->codegen->findEntity((string) $node['entity']);

        $attribute = new Attribute\IntAttribute();
        $attribute->setName($to->getName() . ' ID');
        $this->entity->addAttribute($attribute);

        $relationship = new Relationship\BelongsTo();
        $relationship->setFrom($this->entity);
        $relationship->setTo($to);
        $relationship->setName((string) $node['name'] ?: (string) $node['entity']);
        $this->entity->addRelationship($relationship);
        $to->addRelationship($relationship);
    }
}
