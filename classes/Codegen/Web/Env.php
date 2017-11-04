<?php
namespace Rhino\Codegen\Codegen\Web;

class Env extends \Rhino\Codegen\Codegen\PackageManager
{
    protected $codegen;
    protected $env = [];

    public function __construct(\Rhino\Codegen\Codegen $codegen)
    {
        $this->codegen = $codegen;
    }

    public function writeEnvFile(string $file, array $env)
    {
        $file = $this->codegen->getFile($file);
        $lines = [];
        foreach ($env as $key => $value) {
            $lines[] = $key . ' = ' . $value;
        }
        natcasesort($lines);
        $lines = implode("\n", $lines) . "\n";
        $this->codegen->writeFile($file, $lines);
    }

    public function add(string $key, string $value)
    {
        $this->env[$key] = $value;
    }

    public function generate()
    {
        if (empty($this->env)) {
            $this->codegen->debug('No env variables to write.');
            return;
        }
        $env = $this->loadEnvFile('.env');
        foreach ($this->env as $key => $value) {
            $env[$key] = $value;
        }
        $this->writeEnvFile('.env', $env);
    }

    protected function loadEnvFile(string $file)
    {
        $file = $this->codegen->getFile($file);
        if (!is_file($file)) {
            $this->codegen->log('Could not find file ' . $file);
            return [];
        }
        $contents = file_get_contents($file);
        if (!$contents) {
            $this->codegen->log('Env file was empty ' . $file);
        }
        $env = [];
        $lines = preg_split("/\\r\\n|\\r|\\n/", $contents);
        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }
            if (preg_match('/(?<key>.*?)=(?<value>.*)/', $line, $matches)) {
                $env[trim($matches['key'])] = trim($matches['value']);
            }
        }
        return $env;
    }
}
