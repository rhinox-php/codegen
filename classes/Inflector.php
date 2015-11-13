<?php
namespace Rhino\Codegen;

trait Inflector {

    protected $inflector;

    public function getInflector(): \ICanBoogie\Inflector {
        if (!$this->inflector) {
            $this->inflector = \ICanBoogie\Inflector::get(\ICanBoogie\Inflector::DEFAULT_LOCALE);
        }
        return $this->inflector;
    }

    public function setInflector(\ICanBoogie\Inflector $inflector) {
        $this->inflector = $inflector;
        return $this;
    }

    public function underscore(string $result): string {
        $inflector = $this->getInflector();
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result);
        return $inflector->underscore($result);
    }

    public function hyphenate(string $result): string {
        $inflector = $this->getInflector();
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result);
        return $inflector->hyphenate($result);
    }

    public function camelize(string $result, bool $lowercaseFirstLetter = false): string {
        $inflector = $this->getInflector();
        $result = preg_replace('/\s+/', '_', $result);
        return $inflector->camelize($result, $lowercaseFirstLetter);
    }

    public function pluralize(string $result): string {
        $inflector = $this->getInflector();
        return $inflector->pluralize($result);
    }

}
