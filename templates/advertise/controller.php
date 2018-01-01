<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-implemented'); ?>;

class HomeController extends \<?= $this->getNamespace('controller-admin-generated'); ?>\AbstractController {
    public function home() {
        $this->render('home');
    }
}
