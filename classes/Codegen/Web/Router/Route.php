<?php
namespace Rhino\Codegen\Codegen\Web\Router;

class Route {
    protected $httpMethods = [];
    protected $urlPath;
    protected $controllerClass;
    protected $controllerMethod;

    // Attribute accessors
    public function getHttpMethods(): array {
        return $this->httpMethods;
    }

    public function setHttpMethods(string ...$value): self {
        $this->httpMethods = $value;
        return $this;
    }

    public function getUrlPath(): string {
        return $this->urlPath;
    }

    public function setUrlPath(string $value): self {
        $this->urlPath = $value;
        return $this;
    }

    public function getControllerClass(): string {
        return $this->controllerClass;
    }

    public function setControllerClass(string $value): self {
        $this->controllerClass = $value;
        return $this;
    }

    public function getControllerMethod(): string {
        return $this->controllerMethod;
    }

    public function setControllerMethod(string $value): self {
        $this->controllerMethod = $value;
        return $this;
    }
}
