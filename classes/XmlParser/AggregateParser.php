<?php
namespace Rhino\Codegen\XmlParser;

abstract class AggregateParser extends NodeParser {
    public function preparse(\SimpleXMLElement $node) {
        $this->preparseNode($node);
        foreach ($node->children() as $childNode) {
            $this->codegen->debug('Preparsing node', $childNode->getName());
            $childParser = $this->getChildParser($childNode);
            $childParser->setCodegen($this->codegen);
            $this->preparseChildNode($childNode, $childParser);
            $childParser->preparse($childNode);
        }
    }

    public function parse(\SimpleXMLElement $node) {
        $this->parseNode($node);
        foreach ($node->children() as $childNode) {
            $childParser = $this->getChildParser($childNode);
            $childParser->setCodegen($this->codegen);
            $this->parseChildNode($childNode, $childParser);
            $childParser->parse($childNode);
        }
    }

    private function getChildParser(\SimpleXMLElement $node) {
        $childParsers = $this->getChildParsers();
        if (!isset($childParsers[$node->getName()])) {
            throw new \Exception('Could not find child parser for ' . $node->getName() . ' in ' . get_class($this));
        }
        return $childParsers[$node->getName()];
    }

    public function preparseNode(\SimpleXMLElement $node): void {

    }

    public function parseNode(\SimpleXMLElement $node): void {

    }

    public function preparseChildNode(\SimpleXMLElement $node, NodeParser $childParser): void {

    }

    public function parseChildNode(\SimpleXMLElement $node, NodeParser $childParser): void {

    }
    
    public abstract function getChildParsers(): array;
}
