#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
php -S 0.0.0.0:<?= $this->getPort(); ?> -t $DIR/../public/ $DIR/router.php
