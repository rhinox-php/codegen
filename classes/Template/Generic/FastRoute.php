<?php
namespace Rhino\Codegen\Template\Generic;

class FastRoute extends \Rhino\Codegen\Template\Generic {
    protected $urlPrefix;

    public function generate() {
        $this->codegen->composer->addRepository([
            'type' => 'vcs',
            'url' => 'git@bitbucket.org:rhino-php/rhino-http',
        ]);
        $this->codegen->composer->addDependency('nikic/fast-route', '~1.2');
        $this->codegen->composer->addDependency('rhinox/http', 'dev-master');
        $this->renderTemplate('router', 'router.php', [
            'entities' => $this->codegen->getEntities(),
        ]);
    }

    public function getUrlPrefix() {
        return $this->urlPrefix;
    }

    public function setUrlPrefix($urlPrefix) {
        $this->urlPrefix = $urlPrefix;
        return $this;
    }
}