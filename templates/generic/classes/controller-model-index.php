<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-implemented'); ?>;

class <?= $entity->getClassName(); ?>Controller extends AbstractController {

    public function index() {
        $dataTable = <?= $entity->getClassName(); ?>::getDataTable();
        if ($dataTable->process($this->request, $this->response)) {
            return;
        }
        $this->render('<?= $entity->getFileName(); ?>/index', [
            'dataTable' => $dataTable,
        ]);
    }

}
