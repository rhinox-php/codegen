<?php
namespace Rhino\Codegen;

class Manifest implements \JsonSerializable
{
    protected $codegen;

    /** @var array Files */
    protected $files = [];

    public function __construct(Codegen $codegen)
    {
        $this->codegen = $codegen;
    }

    public function clean(bool $force = false)
    {
        foreach ($this->files as $manifestFile => $hash) {
            $file = $this->codegen->getPath($manifestFile);
            if (!is_file($file)) {
                $this->codegen->log('Manifest file does not exist: ' . $file);
                unset($this->files[$manifestFile]);
                continue;
            }
            $file = realpath($file);
            $currentHash = md5_file($file);
            if ($currentHash !== $hash) {
                $this->codegen->log('Manifest file hash does not match: ' . $file . ' ' . $currentHash . ':' . $hash);
                if (!$force) {
                    continue;
                }
            }
            $this->codegen->log('Deleting ' . $file);
            if (!$this->codegen->isDryRun()) {
                unlink($file);
                unset($this->files[$manifestFile]);
            }
        }
    }

    public function jsonSerialize()
    {
        ksort($this->files);
        return $this->files;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): self
    {
        $this->files = $files;
        return $this;
    }

    public function addFile(string $file): self
    {
        assert(is_file($file), new \Exception('Manifest expects files to exist'));
        $hash = md5_file($file);
        $file = $this->getRelativePath($this->codegen->getPath(), $file);
        $this->files[$file] = $hash;
        return $this;
    }

    private function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
        $from = str_replace('\\', '/', $from);
        $to = str_replace('\\', '/', $to);

        $from = explode('/', $from);
        $to = explode('/', $to);
        $relPath = $to;

        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}
