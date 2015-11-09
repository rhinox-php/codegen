<?php
require_once __DIR__ . '/vendor/autoload.php';

(new Rhino\Codegen\XmlParser(__DIR__ . '/example/person.xml'))->parse();
$cwd = getcwd();
chdir(__DIR__ . '/example/classes');
system('php-cs-fixer fix . --level=psr2 -vvv');
chdir($cwd);
