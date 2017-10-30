<?php
$cwd = getcwd();
chdir(__DIR__);
passthru('../bin/rhino-codegen gen -x');
passthru('../bin/rhino-codegen merge:class --file-1=generated/src/classes/Model/ObjectAttribute.php --file-2=../classes/Attribute/ObjectAttribute.php -x');
passthru('../bin/rhino-codegen merge:class --file-1=generated/src/classes/Model/Template.php --file-2=../classes/Template/Template.php -x');
passthru('../bin/rhino-codegen merge:class --file-1=generated/src/classes/Model/OutputFile.php --file-2=../classes/Template/OutputFile.php -x');
chdir($cwd);
