<?php
namespace Rhino\Codegen\XmlParser;

abstract class NodeParser {
    public abstract function parse(\SimpleXMLElement $node);

    public function preparse(\SimpleXMLElement $node) {
    }
    
    public function setCodegen($codegen) {
        $this->codegen = $codegen;
        return $this;
    }
}
