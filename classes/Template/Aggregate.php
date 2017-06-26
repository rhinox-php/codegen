<?php
namespace Rhino\Codegen\Template;

abstract class Aggregate extends Template {
    public abstract function aggregate();

    public function generate() {
        foreach ($this->iterateTemplates() as $template) {
            $template->generate();
        }
    }

    public function iterateTemplates() {
        foreach ($this->aggregate() as $templateClass) {
            $template = new $templateClass();
            $template->codegen = $this->codegen;
            $template->name = $this->name;
            $template->namespaces = $this->namespaces;
            $template->paths = $this->paths;
            yield $template;
        }
    }
}
