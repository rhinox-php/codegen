<?php
namespace Rhino\Codegen;

trait Inflector {

    protected $inflector = [];

    public function getInflector() {
        if (!$this->inflector) {
            $this->inflector = \ICanBoogie\Inflector::get(\ICanBoogie\Inflector::DEFAULT_LOCALE);
        }
        return $this->inflector;
    }

    public function setInflector($inflector) {
        $this->inflector = $inflector;
        return $this;
    }

}
