<?php
namespace Rhino\Codegen\Template\Generic;

class BinLint extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->codegen->gitIgnore->addIgnore('.php_cs.cache');
        $this->codegen->composer->addDevDependency('friendsofphp/php-cs-fixer', '~2.7');
        $this->renderTemplate('generic/bin/lint-php-syntax-check.sh', 'bin/lint-php-syntax-check.sh')->setExecutable(true);
        $this->renderTemplate('generic/bin/lint-php-fix.sh', 'bin/lint-php-fix.sh')->setExecutable(true);
        $this->renderTemplate('generic/bin/lint-php-check.sh', 'bin/lint-php-check.sh')->setExecutable(true);
    }
}
