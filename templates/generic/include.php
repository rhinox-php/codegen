<?= '<?php'; ?>

if (version_compare(PHP_VERSION, '7.1.0', '<')) {
    die('PHP 7.1+ is required' . PHP_EOL);
}

define('ROOT', __DIR__ . '/');
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/environment/local.php';
