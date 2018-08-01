<?php
namespace Rhino\Codegen\XmlParser;

class DynamicParser extends NodeParser
{
    private $parent;

    public function __construct(\Rhino\Codegen\Node $parent = null)
    {
        $this->parent = $parent;
    }

    public function parse(\SimpleXMLElement $xmlNode)
    {
        $node = new \Rhino\Codegen\Node($xmlNode);
        $this->parent->addChild($node);
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }
}
