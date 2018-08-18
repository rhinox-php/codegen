<?php
namespace Rhino\Codegen;

class XmlParser
{
    protected $file;
    protected $codegen;
    protected $names;

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
            $xml = simplexml_load_file($file);
            if (!$xml) {
                throw new \Exception('Could not read XML: ' . implode(PHP_EOL, array_map(function ($error) {
                    return "$error->line:$error->column $error->message";
                }, libxml_get_errors())));
            }
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

    public function name(Node $node) {
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

    public function setNames(callable $names) {
        $this->names = $names;
        return $this;
    }
}
