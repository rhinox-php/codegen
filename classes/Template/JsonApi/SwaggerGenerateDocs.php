<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerGenerateDocs extends \Rhino\Codegen\Template\Template {
    protected $name = 'json-api';

    public function generate() {
        $this->renderTemplate('bin/generate-docs.bat', 'bin/generate-docs.bat');
        $this->renderTemplate('bin/generate-docs.sh', 'bin/generate-docs.sh');
        $this->renderTemplate('bin/generate-docs.js', 'bin/generate-docs.js');
    }
}
