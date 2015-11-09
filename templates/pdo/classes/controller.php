<?= '<?php'; ?>

namespace <?= $codegen->getNamespace(); ?>\Controller;
use <?= $codegen->getNamespace(); ?>\Model\<?= $entity->getName(); ?>;

class <?= $entity->getName(); ?>Controller {

    public function index() {
        $dataTable = <?= $entity->getName(); ?>::getDataTable();
        if ($dataTable->process($this->request, $this->response)) {
            return;
        }
        $this->render('<?= $entity->getFileName(); ?>/index', [
            'dataTable' => $dataTable,
        ]);
    }

    public function create() {
        $this->form(new <?= $entity->getName(); ?>());
    }

    public function edit() {
        $entity = <?= $entity->getName(); ?>::findById();
        if (!$entity) {
            $this->response->notFound();
            return;
        }
        $this->form($entity);
    }

    protected function form(<?= $entity->getName(); ?> $entity) {
        $this->render('<?= $entity->getFileName(); ?>/form', [
            'entity' => $entity,
        ]);
    }

}
