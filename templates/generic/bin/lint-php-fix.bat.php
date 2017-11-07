#!/bin/bash
pushd %~dp0\..
vendor\bin\php-cs-fixer.bat fix src --rules=@PSR2,ordered_class_elements
vendor\bin\php-cs-fixer.bat fix tests --rules=@PSR2,ordered_class_elements
vendor\bin\php-cs-fixer.bat fix environment --rules=@PSR2,ordered_class_elements
vendor\bin\php-cs-fixer.bat fix bin --rules=@PSR2,ordered_class_elements
popd
