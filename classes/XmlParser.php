<?php

namespace Rhino\Codegen;

use SimpleXMLElement;
use SimpleXMLIterator;

class XmlParser
{
    protected $file;
    protected $codegen;
    protected $names;
    protected array $expanders = [];

    public function __construct(Codegen $codegen, $file)
    {
        assert(is_file($file), 'Expected codegen XML file to be valid: ' . $file);

        $this->file = $file;
        $this->codegen = $codegen;
    }

    public function parse(): Codegen
    {
        $errorMode = libxml_use_internal_errors(true);
        try {
            $file = $this->getFile();
            $xml = new SimpleXMLIterator(file_get_contents($file));
            if (!$xml) {
                throw new \Exception('Could not read XML: ' . implode(PHP_EOL, array_map(function ($error) {
                    return "$error->line:$error->column $error->message";
                }, libxml_get_errors())));
            }
            $this->expand($xml);
            // echo $xml->asXML() . PHP_EOL;
            $node = new Node($xml, $this);
            $this->codegen->node->merge($node);
        } catch (\Exception $exception) {
            throw new \Exception('Error parsing XML in ' . $file, 1, $exception);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($errorMode);
        }

        return $this->codegen;
    }

    private function expand(\SimpleXMLIterator $sxi, \SimpleXMLIterator $parent = null)
    {
        for ($sxi->rewind(); $sxi->valid(); $sxi->next()) {
            [$key, $node] = [$sxi->key(), $sxi->current()];
            if (isset($this->expanders[$key])) {
                $xml = '<root>' . $this->expanders[$key] . '</root>';
                $xml = preg_replace_callback('/{{\s*(?<expression>.*?)\s*}}/', function ($matches) use($node) {
                    foreach (explode('|', $matches['expression']) as $attribute) {
                        $attribute = trim($attribute);
                        if (isset($node[$attribute]) && $node[$attribute]) {
                            return $node[$attribute];
                        }
                    }
                    return $node[$matches['expression']];
                }, $xml);
                $insert = new SimpleXMLIterator($xml);
                for ($insert->rewind(); $insert->valid(); $insert->next()) {
                    $insertedNode = $sxi->addChild($insert->key());
                    foreach ($insert->current()->attributes() as $attributeKey => $attributeValue) {
                        $insertedNode->addAttribute($attributeKey, $attributeValue);
                    }
                }
            }
            if ($sxi->hasChildren()) {
                $this->expand($node, $sxi);
            }
        }
    }

    public function name(Node $node)
    {
        if (!$this->names) {
            return [];
        }
        $method = $this->names;
        return $method($node);
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setNames(callable $names)
    {
        $this->names = $names;
        return $this;
    }

    public function addExpander(string $node, string $expandedXml)
    {
        $this->expanders[$node] = $expandedXml;
        return $this;
    }
}
