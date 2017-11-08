<?php
namespace Rhino\Codegen\Template\Generic;

class TestPhpUnit extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->codegen->gitIgnore->addIgnore('reports');
        $this->codegen->composer->addDevDependency('phpunit/phpunit', '~6.4');
        $this->renderTemplate('generic/bin/test-php-unit.sh', 'bin/test-php-unit.sh')->setExecutable(true);
        $this->renderTemplate('generic/bin/test-php-unit.bat', 'bin/test-php-unit.bat')->setExecutable(true);
        $this->renderTemplate('generic/tests/phpunit.xml', 'tests/phpunit.xml', [
        ]);
    }
}
