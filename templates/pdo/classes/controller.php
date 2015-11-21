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

    public function edit($id) {
        $entity = <?= $entity->getClassName(); ?>::findById($id);
        if (!$entity) {
            $this->response->notFound();
            return;
        }
        $this->form($entity);
    }

    protected function form(<?= $entity->getClassName(); ?> $entity) {
        if ($this->hasInput()) {
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute
    || $attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
            $entity->set<?= $attribute->getMethodName(); ?>($this->getInput('<?= $attribute->getPropertyName(); ?>'));
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
            $entity->set<?= $attribute->getMethodName(); ?>(new \DateTimeImmutable($this->getInput('<?= $attribute->getPropertyName(); ?>')));
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
            $entity->set<?= $attribute->getMethodName(); ?>($this->getInput('<?= $attribute->getPropertyName(); ?>') ? true : false);
<?php endif; ?>
<?php endforeach; ?>

            $entity->save();
            $this->response->redirect('/<?= $entity->getPluralRouteName(); ?>');
            return;
        }

        $this->render('<?= $entity->getFileName(); ?>/form', [
            'entity' => $entity,
        ]);
    }

}
