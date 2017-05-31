<?php
namespace Rhino\Codegen\Template;

abstract class Aggregate extends Template {
    public abstract function aggregate();

    public function generate() {
        foreach ($this->aggregate() as $template) {
            $template->setCodegen($this->getCodegen())->generate();
        }
    }
}
