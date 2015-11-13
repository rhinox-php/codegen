<?= '<?php'; ?>

namespace <?= $codegen->getNamespace(); ?>\Controller;
use <?= $codegen->getNamespace(); ?>\Model\<?= $entity->getClassName(); ?>;

class <?= $entity->getClassName(); ?>Controller extends \Rhino\Core\Controller {

    public function index() {
        $dataTable = <?= $entity->getClassName(); ?>::getDataTable();
        if ($dataTable->process($this->request, $this->response)) {
            return;
        }
        $this->render('<?= $entity->getFileName(); ?>/index', [
            'dataTable' => $dataTable,
        ]);
    }

    public function create() {
        $this->form(new <?= $entity->getClassName(); ?>());
    }

    public function edit() {
        $entity = <?= $entity->getClassName(); ?>::findById();
        if (!$entity) {
            $this->response->notFound();
            return;
        }
        $this->form($entity);
    }

    protected function form(<?= $entity->getClassName(); ?> $entity) {
        $this->render('<?= $entity->getFileName(); ?>/form', [
            'entity' => $entity,
        ]);
    }

}
