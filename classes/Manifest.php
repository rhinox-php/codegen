<?php
namespace Rhino\Codegen;

class Manifest implements \JsonSerializable
{
    protected $codegen;

    /** @var array Files */
    protected $files = [];

    public function __construct(Codegen $codegen) {
        $this->codegen = $codegen;
    }

    public function clean() {
        foreach ($this->files as $file => $hash) {
            $file = $this->codegen->getPath($file);
            if (!is_file($file)) {
                $this->codegen->debug('Manifest file does not exist: ' . $file);
                continue;
            }
            $file = realpath($file);
            $currentHash = md5_file($file);
            if ($currentHash !== $hash) {
                $this->codegen->debug('Manifest file hash does not match: ' . $file . ' ' . $currentHash . ':' . $hash);
                continue;
            }
            $this->codegen->log('Deleting ' . $file);
            if (!$this->codegen->isDryRun()) {
                unlink($file);
            }
        }
    }

    public function jsonSerialize() {
        ksort($this->files);
        return $this->files;
    }

    public function getFiles(): array {
        return $this->files;
    }

    public function setFiles(array $files): self {
        $this->files = $files;
        return $this;
    }

    public function addFile(string $file): self {
        assert(is_file($file), new \Exception('Manifest expects files to exist'));
        $hash = md5_file($file);
        $file = preg_replace('/^' . preg_quote(realpath($this->codegen->getPath()), '/') . '/', '', realpath($file));
        $file = str_replace('\\', '/', $file);
        $file = trim($file, '/');
        $this->files[$file] = $hash;
        return $this;
    }
}
