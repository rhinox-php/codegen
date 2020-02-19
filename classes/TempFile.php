<?php

namespace Rhino\Codegen;

class TempFile
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function __destruct()
    {
        if (is_file($this->path)) {
            @unlink($this->path);
        }
    }

    public static function createUnique()
    {
        return new static(sys_get_temp_dir() . '/' . uniqid('codegen-', true));
    }

    public function getPath(): string
    {
        $this->createDirectory();
        return $this->path;
    }

    public function getHash()
    {
        if (!is_file($this->path)) {
            throw new \Exception('Cannot hash file: ' . $this->path);
        }
        return md5_file($this->path);
    }

    public function createDirectory()
    {
        $directory = dirname($this->path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    public function copyFrom(string $path)
    {
        $this->createDirectory();
        copy($path, $this->path);
    }

    public function putContents($data)
    {
        $this->createDirectory();
        file_put_contents($this->path, $data);
    }

    public function getContents()
    {
        file_get_contents($this->path);
    }

    public function getSize(): int
    {
        return filesize($this->path);
    }
}
