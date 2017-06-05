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
        foreach ($this->aggregate() as $template) {
            yield $template->setCodegen($this->getCodegen());
        }
    }
}
