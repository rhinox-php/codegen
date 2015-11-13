<?= '<?php'; ?>

define('ROOT', __DIR__);
require_once __DIR__ . '/vendor/autoload.php';

$application = new <?= $codegen->getNamespace(); ?>\Application();

require_once __DIR__ . '/environment/local.php';

$application->register();
// $application->loadModule(new \Rhino\Form\System());
$application->loadModule(new \Rhino\DataTable\System());
$application->configure();
