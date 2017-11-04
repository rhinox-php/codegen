<?php
namespace Rhino\Codegen\XmlParser;

abstract class AggregateParser extends NodeParser
{
    protected $parsers = [];

    public function preparse(\SimpleXMLElement $node)
    {
        $this->preparseNode($node);
        foreach ($node->children() as $childNode) {
            $this->codegen->debug('Preparsing node', $childNode->getName());
            $childParser = $this->getParser($childNode->getName());
            $childParser->setCodegen($this->codegen);
            $this->preparseChildNode($childNode, $childParser);
            $childParser->preparse($childNode);
        }
    }

    public function parse(\SimpleXMLElement $node)
    {
        $this->parseNode($node);
        foreach ($node->children() as $childNode) {
            $childParser = $this->getParser($childNode->getName());
            $childParser->setCodegen($this->codegen);
            $this->parseChildNode($childNode, $childParser);
            $childParser->parse($childNode);
        }
    }

    public function preparseNode(\SimpleXMLElement $node): void
    {
    }

    public function parseNode(\SimpleXMLElement $node): void
    {
    }

    public function preparseChildNode(\SimpleXMLElement $node, NodeParser $childParser): void
    {
    }

    public function parseChildNode(\SimpleXMLElement $node, NodeParser $childParser): void
    {
    }

    public function addParser($nodeName, $parser)
    {
        $this->parsers[$nodeName] = $parser;
        return $this;
    }

    public function getParser(string $name)
    {
        if (!isset($this->parsers[$name])) {
            throw new \Exception('Could not find child parser for ' . $name . ' in ' . get_class($this));
        }
        return $this->parsers[$name];
    }
}
