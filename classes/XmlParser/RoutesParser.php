<?php
namespace Rhino\Codegen\XmlParser;

class RoutesParser extends AggregateParser
{
    public function getChildParsers(): array
    {
        return [
            'controller' => new Routes\ControllerParser(),
        ];
    }
}
