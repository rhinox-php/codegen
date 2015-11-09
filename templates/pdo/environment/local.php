<?= '<?php'; ?>

$whoops = new \Whoops\Run();
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
$whoops->register();

$application = new <?= $codegen->getNamespace(); ?>\Application('http://localhost:8001/', function() {
    return new PDO('mysql:host=127.0.0.1;dbname=<?= $codegen->getDatabaseName(); ?>', 'root', 'root', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8 COLLATE utf8_unicode_ci',
    ]);
});
