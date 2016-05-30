<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

abstract class AttributeParser extends \Rhino\Codegen\XmlParser\NodeParser {
    public function setEntity($entity) {
        $this->entity = $entity;
        return $this;
    }
}
