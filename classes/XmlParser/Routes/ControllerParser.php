<?php
namespace Rhino\Codegen\XmlParser\Routes;

class ControllerParser extends \Rhino\Codegen\XmlParser\NodeParser
{
    public function parse(\SimpleXMLElement $node): void
    {
        [$controllerClass, $controllerMethod] = explode('::', (string) $node['controller']);
        $this->codegen->router->addRoute()
            ->setHttpMethods((string) $node['method'] ? explode(',', (string) $node['method']) : ['get'])
            ->setUrlPath((string) $node['url'])
            ->setControllerClass($controllerClass)
            ->setControllerMethod($controllerMethod);
    }
}
