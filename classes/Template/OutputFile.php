<?php
namespace Rhino\Codegen\Template;

class OutputFile
{

    /** @var string|null Path */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function chmod(int $mode): self
    {
        if (is_file($this->path)) {
            chmod($this->path, $mode);
        }
        return $this;
    }

    public function setExecutable(bool $executable): self
    {
        if ($executable) {
            return $this->chmod(0755);
        }
        return $this->chmod(0644);
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
