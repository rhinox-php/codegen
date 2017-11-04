<?php
namespace Rhino\Codegen;

class FormatPhp {
    public static function formatFile(string $file) {
        passthru(__DIR__ . '/../vendor/bin/php-cs-fixer fix ' . $file . ' --rules=@PSR2,ordered_class_elements > /dev/null 2>&1 &');
    }
}
