<?= '<?php'; ?>

namespace <?= $codegen->getNamespace(); ?>\Controller;

class HomeController extends \Rhino\Core\Controller {

    public function home() {
        $this->render('home');
    }

}
