<?php
namespace Rhino\Codegen\Template;

abstract class Aggregate extends Template {

    protected $path = null;

    public abstract function aggregate();

    public function generate() {
        foreach ($this->aggregate() as $template) {
            $template->setCodegen($this->getCodegen())->generate();
        }
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

}
