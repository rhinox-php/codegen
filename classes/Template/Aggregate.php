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
        foreach ($this->aggregate() as $templateClass) {
            $template = new $templateClass();
            $template->codegen = $this->codegen;
            $template->namespaces = $this->namespaces;
            $template->paths = $this->paths;
            yield $template;
        }
    }
}
