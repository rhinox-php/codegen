<?php
namespace Rhino\Codegen;

trait Inflector
{
    protected $inflector;

    public function getInflector(): \ICanBoogie\Inflector
    {
        if (!$this->inflector) {
            $this->inflector = \ICanBoogie\Inflector::get(\ICanBoogie\Inflector::DEFAULT_LOCALE);
        }
        return $this->inflector;
    }

    public function setInflector(\ICanBoogie\Inflector $inflector)
    {
        $this->inflector = $inflector;
        return $this;
    }

    public function underscore($result): string
    {
        $inflector = $this->getInflector();
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result);
        $result = $inflector->underscore($result);
        $result = preg_replace('/([0-9]+)/', '_$1', $result);
        return $result;
    }

    public function hyphenate($result): string
    {
        $inflector = $this->getInflector();
        $result = preg_replace('/\s+/', '_', $result);
        $result = $inflector->camelize($result);
        return $inflector->hyphenate($result);
    }

    public function camelize($result, bool $lowercaseFirstLetter = false): string
    {
        $inflector = $this->getInflector();
        $result = preg_replace('/\s+/', '_', $result);
        return $inflector->camelize($result, $lowercaseFirstLetter);
    }

    public function pluralize($result): string
    {
        $inflector = $this->getInflector();
        return $inflector->pluralize($result);
    }

    public function humanize($result): string
    {
        $inflector = $this->getInflector();
        $result = $inflector->underscore($result);
        return $inflector->humanize($result, true);
    }
}
