<?php
namespace Rhino\Codegen\XmlParser\Entity;
use Rhino\Codegen\Attribute;

class AuthenticationParser extends AttributeParser {
    public function parse(\SimpleXMLElement $node) {
        $this->entity->setAuthentication(true);
        $attribute = new Attribute\StringAttribute();
        $attribute->setName('Password Hash');
        $this->entity->addAttribute($attribute);
    }
}
