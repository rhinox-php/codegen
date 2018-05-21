<?php
namespace Rhino\Codegen;

class FormatPhp
{
    public static function formatFile(string $file)
    {
        if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
            assert(is_file(__DIR__ . '\..\vendor\bin\php-cs-fixer.bat'), new \Exception('Could not find php-cs-fixer, try running composer update.'));
            passthru(__DIR__ . '\..\vendor\bin\php-cs-fixer.bat fix ' . $file . ' --rules=@PSR2,ordered_class_elements > NUL 2> NUL');
        } else {
            assert(is_file(__DIR__ . '/../vendor/bin/php-cs-fixer'), new \Exception('Could not find php-cs-fixer, try running composer update.'));
            passthru('php ' . __DIR__ . '/../vendor/bin/fmt.phar --psr2 ' . $file);
        }
    }
}
