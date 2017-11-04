<?php
namespace Rhino\Codegen\Codegen;

class GitIgnore {
    protected $ignore = [];

    public function __construct(\Rhino\Codegen\Codegen $codegen) {
        $this->codegen = $codegen;
    }

    protected function loadGitIgnoreFile(string $file): array {
        $file = $this->codegen->getFile($file);
        if (!is_file($file)) {
            $this->codegen->log('Could not find file ' . $file);
            return [];
        }
        $contents = file_get_contents($file);
        if (!$contents) {
            $this->codegen->log('Git ignore file was empty ' . $file);
        }
        $ignore = [];
        $lines = preg_split("/\\r\\n|\\r|\\n/", $contents);
        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }
            $ignore[] = trim($line);
        }
        return $ignore;
    }

    public function writeGitIgnoreFile(string $file, $ignore): self {
        $file = $this->codegen->getFile($file);
        $ignore = array_unique($ignore);
        natcasesort($ignore);
        $ignore = implode("\n", $ignore) . "\n";
        $this->codegen->writeFile($file, $ignore);
        return $this;
    }

    public function generate(): self {
        if (empty($this->ignore)) {
            $this->codegen->debug('No ignored paths to write.');
            return $this;
        }
        $ignore = $this->loadGitIgnoreFile('.gitignore');
        foreach ($this->ignore as $path) {
            $ignore[] = $path;
        }
        $this->writeGitIgnoreFile('.gitignore', $ignore);
        return $this;
    }

    public function addIgnore(string $path): self {
        $this->ignore[] = $path;
        return $this;
    }

}
