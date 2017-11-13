<?= '<?php'; ?>

if (version_compare(PHP_VERSION, '7.1.0', '<')) {
    die('PHP 7.1+ is required' . PHP_EOL);
}

ini_set('html_errors', false);

require_once __DIR__ . '/../vendor/autoload.php';

$cache = __DIR__ . '/../reports/code-coverage.php';
if (is_file($cache)) {
    $coverage = require $cache;
}
if (!$coverage) {
    $filter = new \SebastianBergmann\CodeCoverage\Filter();
    $filter->addDirectoryToWhitelist(__DIR__ . '/../src/');
    $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(null, $filter);
}

$coverage->start('Coverage');

require_once __DIR__ . '/../include.php';
require_once __DIR__ . '/../src/router.php';

$coverage->stop(true);

$writer = new \SebastianBergmann\CodeCoverage\Report\PHP();
$writer->process($coverage, __DIR__ . '/../reports/code-coverage.php');

$writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade();
$writer->process($coverage, __DIR__ . '/../reports/code-coverage');
