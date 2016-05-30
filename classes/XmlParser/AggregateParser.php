<?php
namespace Rhino\Codegen\XmlParser;

abstract class AggregateParser extends NodeParser {
    public function parse(\SimpleXMLElement $node) {
        $this->parseNode($node);
        $children = $this->getChildParsers();
        foreach ($node->children() as $child) {
            if (!isset($children[$child->getName()])) {
                throw new \Exception('Could not find child parser for ' . $child->getName());
            }
            $this->parseChild($child, $children[$child->getName()]->setCodegen($this->codegen))->parse($child);
        }
    }
    
    public abstract function getChildParsers(): array;
}
