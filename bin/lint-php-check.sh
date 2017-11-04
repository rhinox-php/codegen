#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
pushd $DIR/..
vendor/bin/php-cs-fixer fix classes --dry-run --rules=@PSR2,ordered_class_elements
vendor/bin/php-cs-fixer fix templates --dry-run --rules=@PSR2,ordered_class_elements
vendor/bin/php-cs-fixer fix bin --dry-run --rules=@PSR2,ordered_class_elements
popd
