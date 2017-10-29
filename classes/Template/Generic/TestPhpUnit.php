<?php
namespace Rhino\Codegen\Template\Generic;

class TestPhpUnit extends \Rhino\Codegen\Template\Generic {
    public function generate() {
        $this->codegen->composer->addDevDependency('phpunit/phpunit', '~6.4');
        $this->renderTemplate('generic/test-php-unit', 'tests/phpunit.xml', [
        ]);
    }
}
