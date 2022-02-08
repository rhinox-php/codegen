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
            if ((fileperms($this->path) & 0777) !== $mode) {
                chmod($this->path, $mode);
            }
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

    public function getContents(): string
    {
        return file_get_contents($this->path);
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

    public function getExtension(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }
}
