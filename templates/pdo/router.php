<?= '<?php'; ?>

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_file(__DIR__ . '/public/' . $url)) {
    return false;
}

require __DIR__ . '/public/index.php';
