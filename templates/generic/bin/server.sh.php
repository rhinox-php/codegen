#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
php -S 127.0.0.1:<?= $this->getPort(); ?> -t $DIR/../public/ $DIR/router.php
