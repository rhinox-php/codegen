<?= '<?php'; ?>

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (is_file(__DIR__ . '/../public/' . $url)) {
    return false;
}
if ($url !== '/' && is_dir(__DIR__ . '/../public/' . $url)) {
    if (!preg_match('~[^/]/$~', $url)) {
        header('Location: ' . rtrim($url, '/') . '/');
        return;
    }
    foreach (new DirectoryIterator(__DIR__ . '/../public/' . $url) as $fileInfo) {
        if ($fileInfo->getFilename() === '.') {
            continue;
        }
        echo '<a href="./' . htmlspecialchars($fileInfo->getFilename(), ENT_QUOTES) . '">' . $fileInfo->getFilename() . '</a><br />' . PHP_EOL;
    }
    return;
}

require __DIR__ . '/../public/index.php';
