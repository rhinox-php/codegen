<?php
namespace Rhino\Codegen\Template;

class OutputFile
{

    /** @var string|null Path */
    protected $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function chmod() {
        // @todo
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $value): self
    {
        $this->path = $value;
        return $this;
    }
}
