<?php
namespace Rhino\Codegen\Template\Generic;

class Composer extends \Rhino\Codegen\Template\Generic
{
    public function generate()
    {
        $this->codegen->composer->addAutoload('psr-4', $this->codegen->getNamespace(), 'src/classes');
    }
}
