#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
pushd $DIR/..
vendor/bin/php-cs-fixer fix classes --rules=@PSR2,ordered_class_elements
vendor/bin/php-cs-fixer fix templates --rules=@PSR2,ordered_class_elements
vendor/bin/php-cs-fixer fix bin --rules=@PSR2,ordered_class_elements
popd
