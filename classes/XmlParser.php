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

        $this->addParser('codegen', new XmlParser\CodegenParser());
        $this->addParser('entity', new XmlParser\EntityParser());
        $this->addParser('routes', new XmlParser\RoutesParser());
    }

    public function parse(): Codegen {
        $errorMode = libxml_use_internal_errors(true);
        try {
            $file = $this->getFile();
            $xml = simplexml_load_file($file);
            if (!$xml) {
                throw new \Exception('Could not read XML: ' . implode(PHP_EOL, array_map(function($error) {
                    return "$error->line:$error->column $error->message";
                }, libxml_get_errors())));
            }
            $this->preparseNode($xml);
            $this->parseNode($xml);
            // var_dump($xml->getName());die();
            // foreach ($xml as $child) {
            //     $this->preparseNode($child);
            // }
            // foreach ($xml as $child) {
            //     $this->parseNode($child);
            // }
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
        $this->codegen->log('Pre-parsing node', $node->getName(), 'with', get_class($this->parsers[$node->getName()]));
        $this->parsers[$node->getName()]->setCodegen($this->codegen)->preparse($node);
    }

    protected function parseNode(\SimpleXMLElement $node) {
        if (!isset($this->parsers[$node->getName()])) {
            throw new \Exception('Could not find node parser for ' . $node->getName());
        }
        $this->codegen->log('Parsing node', $node->getName(), 'with', get_class($this->parsers[$node->getName()]));
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
