<?php
namespace Rhino\Codegen\Template\JsonApi;

class SwaggerGenerateDocs extends \Rhino\Codegen\Template\Template {
    public function generate() {
        $this->renderTemplate('json-api/bin/generate-docs.bat', 'bin/generate-docs.bat');
        $this->renderTemplate('json-api/bin/generate-docs.sh', 'bin/generate-docs.sh');
        $this->renderTemplate('json-api/bin/generate-docs.js', 'bin/generate-docs.js');
    }
}
