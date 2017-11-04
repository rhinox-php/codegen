<?php
namespace Rhino\Codegen\XmlParser\Entity;

use Rhino\Codegen\Relationship;

class ReferenceParser extends AttributeParser
{
    public function parse(\SimpleXMLElement $node)
    {
        $to = $this->codegen->findEntity((string) $node['entity']);

        $relationship = new Relationship\Reference();
        $relationship->setFrom($this->entity);
        $relationship->setTo($to);
        $relationship->setName((string) $node['name'] ?: $to->getName());
        $this->entity->addRelationship($relationship);
        $to->addRelationship($relationship);
    }
}
