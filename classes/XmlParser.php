<?php
namespace Rhino\Codegen;

class XmlParser {

    protected $file;
    protected $codegen;
    protected $parsers = [];

    public function __construct(Codegen $codegen, $file) {
        assert(is_file($file), 'Expected codegen XML file to be valid: ' . $file);

        $this->file = $file;
        $this->codegen = $codegen;

        $this->addParser('entity', new XmlParser\EntityParser());
    }

    public function parse(): Codegen {
        $errorMode = libxml_use_internal_errors(true);
        try {
            $file = $this->getFile();
            $xml = simplexml_load_file($file);
            if (!$xml) {
                throw new \Exception('Could not read XML.');
            }
            foreach ($xml as $child) {
                $this->preparseNode($child);
            }
            foreach ($xml as $child) {
                $this->parseNode($child);
            }
        } catch (\Exception $exception) {
            throw new \Exception('Error parsing XML in ' . $file, 1, $exception);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($errorMode);
        }

        return $this->codegen;
    }

    protected function preparseNode($node) {
        if (!isset($this->parsers[$node->getName()])) {
            throw new \Exception('Could not find node parser for ' . $node->getName());
        }
        $this->parsers[$node->getName()]->setCodegen($this->codegen)->preparse($node);
    }

    protected function parseNode(\SimpleXMLElement $node) {
        if (!isset($this->parsers[$node->getName()])) {
            throw new \Exception('Could not find node parser for ' . $node->getName());
        }
        $this->parsers[$node->getName()]->setCodegen($this->codegen)->parse($node);
    }

    public function getFile() {
        return $this->file;
    }

    public function addParser($nodeName, $parser) {
        $this->parsers[$nodeName] = $parser;
        return $this;
    }

}
