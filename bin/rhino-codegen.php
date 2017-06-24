<?php
if (!class_exists('Rhino\Codegen\Codegen')) {
    if (is_file(__DIR__ . '/../vendor/autoload.php')) {
        require __DIR__ . '/../vendor/autoload.php';
    } elseif (is_file(__DIR__ . '/../../../autoload.php')) {
        require __DIR__ . '/../../../autoload.php';
    } else {
        throw new Exception('Cannot file autoloader, tried ' . __DIR__ . '/../vendor/autoload.php and ' . __DIR__ . '/../../../autoload.php');
    }
}
(new \Rhino\Codegen\Cli\Application())->run();
