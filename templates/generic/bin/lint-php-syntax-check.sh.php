#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
pushd $DIR/..
find ./src -name "*.php" -print0 | xargs -0 -n1 -P8 php --syntax-check 1> /dev/null
find ./tests -name "*.php" -print0 | xargs -0 -n1 -P8 php --syntax-check 1> /dev/null
find ./environment -name "*.php" -print0 | xargs -0 -n1 -P8 php --syntax-check 1> /dev/null
find ./bin -name "*.php" -print0 | xargs -0 -n1 -P8 php --syntax-check 1> /dev/null
popd
