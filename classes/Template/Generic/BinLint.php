<?php
namespace Rhino\Codegen\Template\Generic;

class BinLint extends \Rhino\Codegen\Template\Generic {

    public function generate() {
        $this->renderTemplate('generic/bin/lint-php-syntax-check.sh', 'bin/lint-php-syntax-check.sh')->chmod('+x');
    }

}
