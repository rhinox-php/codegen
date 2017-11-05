<?php
namespace Rhino\Codegen\Template\Generic;

class BinTest extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->renderTemplate('generic/bin/test-full.sh', 'bin/test-full.sh')->setExecutable(true);
    }
}
