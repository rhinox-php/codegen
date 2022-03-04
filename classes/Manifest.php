<?php

namespace Rhino\Codegen;

class Manifest
{
    public function __construct(
        private Codegen $codegen,
        private string $manifestFile
    ) {
    }

    public function clean(bool $force = false)
    {
        $this->lock(function ($manifest) use ($force) {
            foreach ($manifest as $file => $hash) {
                $file = $this->codegen->getPath($file);
                if (!is_file($file)) {
                    $this->codegen->log('Manifest file does not exist: ' . $file);
                    unset($manifest[$file]);
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
                    unset($manifest[$file]);
                }
            }
            return $manifest;
        });
    }

    public function addFile(string $file, array $hashes): self
    {
        assert(is_file($file), new \Exception('Manifest expects files to exist: ' . $file));
        $file = $this->getRelativePath($this->codegen->getPath(), realpath($file));
        $this->lock(function ($manifest) use ($file, $hashes) {
            if (isset($manifest[$file])) {
                $this->codegen->debug('Updating file in manifest', $file, $manifest[$file], '->', $hashes);
                $manifest[$file] = $hashes;
            } else {
                $this->codegen->debug('Adding file to manifest', $file, $hashes);
                $manifest[$file] = $hashes;
            }
            ksort($manifest);
            return $manifest;
        });
        return $this;
    }

    public function getHashes(string $file): array
    {
        $file = $this->getRelativePath($this->codegen->getPath(), realpath($file));
        $manifest = $this->readManifest();
        return isset($manifest[$file]) && is_array($manifest[$file]) ? $manifest[$file] : [];
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
        $relPath = implode('/', $relPath);
        $relPath = preg_replace('~^./~', '', $relPath);
        $relPath = str_replace('//', '/', $relPath);
        return $relPath;
    }

    private function readManifest(): array
    {
        return json_decode(file_get_contents($this->manifestFile), true) ?: [];
    }

    private function lock(callable $callback)
    {
        $handle = fopen($this->manifestFile, 'r+');
        if (!$handle) {
            throw new \Exception('Unable to open manifest file: ' . $this->manifestFile);
        }
        try {
            if (flock($handle, LOCK_EX)) {
                $size = filesize($this->manifestFile);
                if ($size > 0) {
                    $content = fread($handle, $size);
                    $json = json_decode($content, true);
                } else {
                    $json = [];
                }
                $json = $callback($json);
                ftruncate($handle, 0);
                fseek($handle, 0);
                fwrite($handle, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                flock($handle, LOCK_UN);
            } else {
                throw new \Exception('Could not lock manifest file: ' . $this->manifestFile);
            }
        } finally {
            if ($handle && is_resource($handle)) {
                @fclose($handle);
            }
        }
    }
}
