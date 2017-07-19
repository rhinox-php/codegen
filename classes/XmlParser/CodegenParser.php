<?php
namespace Rhino\Codegen\XmlParser;

class CodegenParser extends AggregateParser {
    
    public function getChildParsers(): array {
        return [
            'entity' => new EntityParser(),
        ];
    }
}
