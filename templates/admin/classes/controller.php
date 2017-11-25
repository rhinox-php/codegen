<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-admin-generated'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>;
use <?= $this->getNamespace('model-serializer'); ?>\<?= $entity->getClassName(); ?>Serializer;
use <?= $this->getNamespace('data-table-admin-generated'); ?>\<?= $entity->getClassName(); ?>DataTable;

class <?= $entity->getClassName(); ?>AdminController extends AbstractController {

    public function index() {
        $dataTable = new <?= $entity->getClassName(); ?>DataTable();
        // $dataTable = <?= $entity->getClassName(); ?>::getDataTable();
        if ($dataTable->process($this->request, $this->response)) {
            return;
        }
        $this->render('admin/<?= $entity->getFileName(); ?>/index', [
            'dataTable' => $dataTable,
        ]);
    }

    public function create() {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        return $this->form($<?= $entity->getPropertyName(); ?>);
    }

    public function edit($id) {
        $<?= $entity->getPropertyName(); ?> = <?= $entity->getClassName(); ?>::findById($id);
        if (!$<?= $entity->getPropertyName(); ?>) {
            $this->response->notFound();
            return;
        }
        return $this->form($<?= $entity->getPropertyName(); ?>);
    }

    public function form(<?= $entity->getClassName(); ?> $<?= $entity->getPropertyName(); ?>) {
        if ($this->canUpdate($<?= $entity->getPropertyName(); ?>)) {
            $this->updateAttributes($<?= $entity->getPropertyName(); ?>);

            $constraint = new Constraints\Collection([
                'fields' => [
                ],
            ]);

            if ($this->validateConstraint($constraint)) {
                $<?= $entity->getPropertyName(); ?>->save();
                $this->response->flashMessage('Successfully saved <?= $entity->getLabel(); ?>.', 'success');
                $this->response->redirect('/admin/<?= $entity->getRouteName(); ?>/' . $<?= $entity->getPropertyName(); ?>->getId());
                return;
            }
        }

        $this->render('admin/<?= $entity->getFileName(); ?>/form', [
            '<?= $entity->getPropertyName(); ?>' => $<?= $entity->getPropertyName(); ?>,
        ]);
    }

    public function canUpdate(<?= $entity->getClassName(); ?> $<?= $entity->getPropertyName(); ?>) {
        if ($this->hasInput()) {
            return true;
        }
        return false;
    }

    public function updateAttributes(<?= $entity->getClassName(); ?> $<?= $entity->getPropertyName(); ?>) {
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String', 'Text'])): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->string('data.attributes.<?= $attribute->getPropertyName(); ?>'));
<?php elseif ($attribute->is(['Date'])): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->dateTime('data.attributes.<?= $attribute->getPropertyName(); ?>'));
<?php elseif ($attribute->is(['DateTime'])): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->dateTime('data.attributes.<?= $attribute->getPropertyName(); ?>'));
<?php elseif ($attribute->is(['Bool'])): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->bool('data.attributes.<?= $attribute->getPropertyName(); ?>'));
<?php elseif ($attribute->is(['Int'])): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->int('data.attributes.<?= $attribute->getPropertyName(); ?>'));
<?php elseif ($attribute->is(['Decimal'])): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->decimal('data.attributes.<?= $attribute->getPropertyName(); ?>'));
<?php endif; ?>
<?php endforeach; ?>
    }

}
