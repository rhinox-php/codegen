<?php
$cwd = getcwd();
chdir(__DIR__);
passthru('codegen gen -x');
passthru('codegen merge:class --file-1=generated/src/classes/Model/ObjectAttribute.php --file-2=../classes/Attribute/ObjectAttribute.php -x');
passthru('codegen merge:class --file-1=generated/src/classes/Model/Template.php --file-2=../classes/Template/Template.php -x');
chdir($cwd);
