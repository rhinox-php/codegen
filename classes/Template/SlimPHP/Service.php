<?php
namespace Rhino\Codegen\Template\SlimPHP;

use Rhino\Codegen\Template\SlimPHP;

class Service extends SlimPHP {

    protected $template = 'classes/services';

    public function generate() {
        $this->renderTemplate($this->getTemplate() . '/json-api-list', 'JsonApiList.php');
        $this->renderTemplate($this->getTemplate() . '/input-data', 'InputData.php');
        $this->renderTemplate($this->getTemplate() . '/jwt', 'Jwt.php');
    }

}
