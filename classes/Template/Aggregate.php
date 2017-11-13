<?php
namespace Rhino\Codegen\Template;

trait Aggregate
{
    protected $name;

    abstract public function aggregate();

    public function generate()
    {
        foreach ($this->iterateTemplates() as $template) {
            $template->generate();
        }
    }

    public function iterateTemplates()
    {
        yield from $this->aggregate();
    }

    protected function aggregateClass(string $templateClass, array $parameters = []): Template {
        $template = new $templateClass(...$parameters);
        $template->codegen = $this->codegen;
        $template->namespaces = $this->namespaces;
        $template->paths = $this->paths;
        return $template;
    }
}
