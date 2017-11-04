<?php
namespace Rhino\Codegen\XmlParser;

class CodegenParser extends AggregateParser
{
    public function __construct()
    {
        $this->addParser('entity', new EntityParser());
    }
}
