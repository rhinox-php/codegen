<?php
namespace Rhino\Codegen\Watch;

class Watcher {
    protected $callback;
    protected $directories;
    protected $ignore = [
        '/^\./',
        '/^vendor$/',
    ];
    protected $lastKey = null;

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    public function start() {
        while (true) {
            $this->scan();
            sleep(1);
        }
    }

    public function scan() {
        $files = [];
        $directories = $this->directories;
        while (!empty($directories)) {
            $directory = array_shift($directories);
            foreach (scandir($directory) as $file) {
                usleep(1);
                if ($this->isIgnored($file)) {
                    continue;
                }
                $file = realpath($directory . '/' . $file);
                if (is_dir($file)) {
                    $directories[] = $file;
                    continue;
                }
                $files[$file] = filemtime($file);
            }
        }
        ksort($files);
        $key = md5(implode(':', $files));
        if ($key != $this->lastKey) {
            $this->triggerCallback();
        }
        $this->lastKey = $key;
    }

    public function isIgnored($file) {
        foreach ($this->ignore as $ignore) {
            if (preg_match($ignore, $file)) {
                return true;
            }
        }
        return false;
    }

    public function triggerCallback() {
        call_user_func($this->callback);
    }

    public function addDirectory(string $directory): self {
        $this->directories[] = realpath($directory);
        return $this;
    }
}
