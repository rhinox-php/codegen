#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
php -S 0.0.0.0:3000 -t $DIR/../public/ $DIR/router-coverage.php
