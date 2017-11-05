#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
rm -f $DIR/../reports/code-coverage.php
rm -rf $DIR/../reports/code-coverage
$DIR/test-php-unit.sh
nohup php -S 127.0.0.1:3000 -t $DIR/../public/ $DIR/router-coverage.php > /dev/null 2>&1 &
serverPid=$!
$DIR/test-api.sh
kill $serverPid
