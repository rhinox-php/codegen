<?php

namespace Rhino\Codegen\Watch;

class Watcher
{
    const METHOD_MODIFIED_TIME = 'modified-time';
    const METHOD_HASH = 'hash';

    protected $callback;
    protected $directories;
    protected $method = self::METHOD_MODIFIED_TIME;
    protected $sleepTime;
    protected $ignore = [
        '/^\./',
        '/^vendor$/',
        '/^node_modules$/',
        '/^bower_components$/',
        '/^storage$/',
    ];
    protected $lastKey = null;
    protected $lastFiles = [];

    public function __construct(callable $callback, int $sleepTime = 2)
    {
        $this->callback = $callback;
        $this->sleepTime = $sleepTime;
    }

    public function start()
    {
        while (true) {
            $this->scan();
            sleep(1);
        }
    }

    public function scan()
    {
        echo '.';
        [$files, $key] = $this->scanFiles();
        $changed = [];
        foreach ($files as $file => $check) {
            if (!isset($this->lastFiles[$file])) {
                echo PHP_EOL . '+' . $check . ':' . $file;
                $changed[$file] = $check;
            } elseif ($this->lastFiles[$file] != $check) {
                echo PHP_EOL . '#' . $check . '/' . $this->lastFiles[$file] . ':' . $file . PHP_EOL;
                $changed[$file] = $check;
            }
        }
        foreach ($this->lastFiles as $file => $check) {
            if (!isset($files[$file])) {
                echo PHP_EOL . '-' . $check . ':' . $file . PHP_EOL;
                $changed[$file] = $check;
            }
        }
        // echo count($files) . PHP_EOL;
        ksort($files);
        $key = md5(implode(':', $files));
        if ($key != $this->lastKey) {
            echo PHP_EOL;
            $this->triggerCallback(array_keys($changed), $files);
        }
        // Re-scan files after running callback
        [$files, $key] = $this->scanFiles();
        $this->lastKey = $key;
        $this->lastFiles = $files;
    }

    private function scanFiles(): array
    {
        $files = [];
        $directories = $this->directories;
        while (!empty($directories)) {
            $directory = array_shift($directories);
            foreach (scandir($directory) as $file) {
                usleep($this->sleepTime);
                if ($this->isIgnored($file)) {
                    continue;
                }
                $file = realpath($directory . '/' . $file);
                if (is_dir($file)) {
                    $directories[] = $file;
                    // echo '*' . $file . PHP_EOL;
                    continue;
                }
                $files[$file] = $this->checkFile($file);
            }
        }
        ksort($files);
        $key = md5(implode(':', $files));
        return [$files, $key];
    }

    protected function checkFile(string $file)
    {
        switch ($this->method) {
            case static::METHOD_MODIFIED_TIME:
                return filemtime($file);

            case static::METHOD_HASH:
                return md5_file($file);
        }
    }

    public function isIgnored($file)
    {
        foreach ($this->ignore as $ignore) {
            if (preg_match($ignore, $file)) {
                return true;
            }
        }
        return false;
    }

    public function triggerCallback(array $changed, array $files): self
    {
        call_user_func($this->callback, $changed, $files);
        return $this;
    }

    public function addDirectory(string $directory): self
    {
        $directory = realpath($directory);
        echo 'Watching ' . $directory . PHP_EOL;
        $this->directories[] = $directory;
        return $this;
    }
}
