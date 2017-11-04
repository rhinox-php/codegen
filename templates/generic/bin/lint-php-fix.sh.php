#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
pushd $DIR/..
vendor/bin/php-cs-fixer fix src --rules=@PSR2,ordered_class_elements
vendor/bin/php-cs-fixer fix tests --rules=@PSR2,ordered_class_elements
vendor/bin/php-cs-fixer fix environment --rules=@PSR2,ordered_class_elements
vendor/bin/php-cs-fixer fix bin --rules=@PSR2,ordered_class_elements
popd
