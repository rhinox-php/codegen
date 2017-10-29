<?php
namespace Rhino\Codegen\Template\Generic;

class TestCoverage extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->codegen->composer->addDevDependency('phpunit/php-code-coverage', '~5.2');
        $this->renderTemplate('generic/bin/server-coverage.sh', 'bin/server-coverage.sh');
        $this->renderTemplate('generic/bin/router-coverage', 'bin/router-coverage.php');
        $this->renderTemplate('generic/tests/coverage', 'tests/coverage.php');
    }
}
