<?php
namespace Rhino\Codegen\Template\Generic;

class Bootstrap extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->codegen->composer->addDependency('filp/whoops', '~2.1');
        $this->renderTemplate('generic/include', 'include.php', []);
        $this->renderTemplate('generic/environment/local', 'environment/local.php', []);
    }
}
