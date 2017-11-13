<?php
namespace Rhino\Codegen\Template\JsonApi;

class Swagger extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\AggregateInterface
{
    use \Rhino\Codegen\Template\Aggregate;

    protected $jsonApi;

    public function __construct(JsonApi $jsonApi) {
        $this->jsonApi = $jsonApi;
    }

    public function aggregate()
    {
        yield $this->aggregateClass(SwaggerYaml::class, [$this->jsonApi]);
        yield $this->aggregateClass(SwaggerYamlInitial::class, [$this->jsonApi]);
        yield $this->aggregateClass(SwaggerGenerateDocs::class, [$this->jsonApi]);
    }
}
