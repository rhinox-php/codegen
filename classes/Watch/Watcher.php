<?php
namespace Rhino\Codegen\Watch;

class Watcher
{
    const METHOD_MODIFIED_TIME = 'modified-time';
    const METHOD_HASH = 'hash';

    protected $callback;
    protected $directories;
    protected $method = self::METHOD_MODIFIED_TIME;
    protected $sleepTime = 2;
    protected $ignore = [
        '/^\./',
        '/^vendor$/',
    ];
    protected $lastKey = null;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
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
                    continue;
                }
                $files[$file] = $this->checkFile($file);
            }
        }
        ksort($files);
        $key = md5(implode(':', $files));
        if ($key != $this->lastKey) {
            $this->triggerCallback(array_keys($files));
        }
        $this->lastKey = $key;
    }

    protected function checkFile(string $file) {
        switch ($this->method) {
            case static::METHOD_MODIFIED_TIME: {
                return filemtime($file);
            }
            case static::METHOD_HASH: {
                return md5_file($file);
            }
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

    public function triggerCallback(array $changed): self
    {
        call_user_func($this->callback, $changed);
        return $this;
    }

    public function addDirectory(string $directory): self
    {
        $this->directories[] = realpath($directory);
        return $this;
    }
}
