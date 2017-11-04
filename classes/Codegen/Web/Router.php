<?php
namespace Rhino\Codegen\Codegen\Web;

class Router
{
    protected $codegen;
    protected $routes = [];

    public function __construct(\Rhino\Codegen\Codegen $codegen)
    {
        $this->codegen = $codegen;
    }

    public function generate()
    {
    }

    public function addRoute()
    {
        $route = new Router\Route();
        $this->routes[] = $route;
        return $route;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function sort()
    {
        usort($this->routes, function ($a, $b) {
            return strnatcasecmp($a->getUrlPath(), $b->getUrlPath());
        });
        return $this;
    }
}
