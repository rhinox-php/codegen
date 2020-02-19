<?php

namespace Rhino\Codegen;

class FormatPhp
{
    public static function formatFile(string $file)
    {
        if (!is_file($file)) {
            throw new \Exception('File not found to format: ' . $file);
        }
        if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
            assert(is_file(__DIR__ . '\..\vendor\bin\php-cs-fixer.bat'), new \Exception('Could not find php-cs-fixer, try running composer update.'));
            passthru(__DIR__ . '\..\vendor\bin\php-cs-fixer.bat fix ' . $file . ' --rules=@PSR2,ordered_class_elements > NUL 2> NUL');
        } else {
            assert(is_file(__DIR__ . '/../vendor/bin/php-cs-fixer'), new \Exception('Could not find php-cs-fixer, try running composer update.'));
            // @todo detect php bin
            passthru('php ' . __DIR__ . '/../vendor/bin/fmt.phar --psr2 ' . $file . ' > /dev/null 2>&1');
        }
    }

    public static function formatString(string $fileContent): string
    {
        return $fileContent;
    }
}
