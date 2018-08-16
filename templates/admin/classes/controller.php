<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-admin-generated'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>;
use <?= $this->getNamespace('model-serializer'); ?>\<?= $entity->class; ?>Serializer;
use <?= $this->getNamespace('data-table-admin-generated'); ?>\<?= $entity->class; ?>DataTable;
use Symfony\Component\Validator\Constraints;

class <?= $entity->class; ?>AdminController extends AbstractController {

    public function index() {
        $dataTable = new <?= $entity->class; ?>DataTable();
        // $dataTable = <?= $entity->class; ?>::getDataTable();
        if ($dataTable->process($this->request, $this->response)) {
            return;
        }
        $this->render('admin/<?= $entity->getFileName(); ?>/index', [
            'dataTable' => $dataTable,
        ]);
    }

    public function create() {
        $<?= $entity->property; ?> = new <?= $entity->class; ?>();
        return $this->form($<?= $entity->property; ?>);
    }

    public function edit($id) {
        $<?= $entity->property; ?> = <?= $entity->class; ?>::findById($id);
        if (!$<?= $entity->property; ?>) {
            $this->response->notFound();
            return;
        }
        return $this->form($<?= $entity->property; ?>);
    }

    public function form(<?= $entity->class; ?> $<?= $entity->property; ?>) {
        if ($this->canUpdate($<?= $entity->property; ?>)) {
            $this->updateAttributes($<?= $entity->property; ?>);

            $constraint = new Constraints\Collection([
                'fields' => [
                ],
            ]);

            if ($this->validateConstraint($constraint)) {
                $<?= $entity->property; ?>->save();
                $this->response->flashMessage('Successfully saved <?= $entity->label; ?>.', 'success');
                $this->response->redirect('/admin/<?= $entity->route; ?>/' . $<?= $entity->property; ?>->getId());
                return;
            }
        }

        $this->render('admin/<?= $entity->getFileName(); ?>/form', [
            '<?= $entity->property; ?>' => $<?= $entity->property; ?>,
        ]);
    }

    public function canUpdate(<?= $entity->class; ?> $<?= $entity->property; ?>) {
        if ($this->hasInput()) {
            return true;
        }
        return false;
    }

    public function updateAttributes(<?= $entity->class; ?> $<?= $entity->property; ?>) {
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->string('<?= $attribute->property; ?>'<?= $attribute->nullable ? ', null' : ''; ?>));
<?php elseif ($attribute->is('date')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->dateTime('<?= $attribute->property; ?>'<?= $attribute->nullable ? ', null' : ''; ?>));
<?php elseif ($attribute->is('date-time')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->dateTime('<?= $attribute->property; ?>'<?= $attribute->nullable ? ', null' : ''; ?>));
<?php elseif ($attribute->is('bool')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->bool('<?= $attribute->property; ?>'<?= $attribute->nullable ? ', null' : ''; ?>));
<?php elseif ($attribute->is('int')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->int('<?= $attribute->property; ?>'<?= $attribute->nullable ? ', null' : ''; ?>));
<?php elseif ($attribute->is('decimal')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->decimal('<?= $attribute->property; ?>'<?= $attribute->nullable ? ', null' : ''; ?>));
<?php endif; ?>
<?php endforeach; ?>
    }

}
