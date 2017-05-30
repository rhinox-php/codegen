<?php
namespace Rhino\Codegen\Template\SlimPhp;

use Rhino\Codegen\Template\SlimPhp;

class Service extends SlimPhp {

    protected $template = 'classes/services';

    public function generate() {
        $this->renderTemplate($this->getTemplate() . '/json-api-list', 'JsonApiList.php');
        $this->renderTemplate($this->getTemplate() . '/input-data', 'InputData.php');
        $this->renderTemplate($this->getTemplate() . '/jwt', 'Jwt.php');
    }

}
