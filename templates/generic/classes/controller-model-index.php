<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-implemented'); ?>;

class <?= $entity->class; ?>Controller extends AbstractController {

    public function index() {
        $dataTable = <?= $entity->class; ?>::getDataTable();
        if ($dataTable->process($this->request, $this->response)) {
            return;
        }
        $this->render('<?= $entity->getFileName(); ?>/index', [
            'dataTable' => $dataTable,
        ]);
    }

}
