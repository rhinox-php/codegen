<?= '<?php'; ?>

namespace <?= $codegen->getNamespace(); ?>;

class Application extends \Rhino\Core\Application {

    protected $root = ROOT;
    protected $namespace = __NAMESPACE__;

}
