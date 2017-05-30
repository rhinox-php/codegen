<?php
namespace Rhino\Codegen\Template\SlimPhp;

use Rhino\Codegen\Template\SlimPhp;

class SlimTrait extends SlimPhp {

    protected $template = 'classes/traits';

    public function generate() {
        $this->renderTemplate($this->getTemplate() . '/trait-jwt', 'JwtTrait.php');
    }

}
