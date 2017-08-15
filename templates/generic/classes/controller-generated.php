<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-generated'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>;

class <?= $entity->getClassName(); ?>Controller extends \<?= $this->getNamespace('controller-implemented'); ?>\AbstractController {

    public function index() {
        $dataTable = $this->getDataTable();
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

    protected function form(<?= $entity->getClassName(); ?> $<?= $entity->getPropertyName(); ?>) {
        if ($this->hasInput()) {
            $this->processInput($<?= $entity->getPropertyName(); ?>);
            if ($this->validate($<?= $entity->getPropertyName(); ?>)) {
                $<?= $entity->getPropertyName(); ?>->save();
                $this->response->redirect('/<?= $entity->getRouteName(); ?>/edit/' . $<?= $entity->getPropertyName(); ?>->getId());
                return;
            }
        }

        $this->render('<?= $entity->getFileName(); ?>/form', [
            'entity' => $entity,
        ]);
    }

    protected function getDataTable(): \Rhino\DataTable\DataTable {
        return <?= $entity->getClassName(); ?>::getDataTable();
    }

    protected function processInput(<?= $entity->getClassName(); ?> $<?= $entity->getPropertyName(); ?>): void {
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute
    || $attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->string('<?= $attribute->getPropertyName(); ?>'));
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>(new \DateTimeImmutable($this->input->dateTime('<?= $attribute->getPropertyName(); ?>')));
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->bool('<?= $attribute->getPropertyName(); ?>') ? true : false);
<?php endif; ?>
<?php endforeach; ?>
    }

    public function validate(): bool {
        return true;
    }
}
