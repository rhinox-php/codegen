<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-generated'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>;

class <?= $entity->class; ?>Controller extends \<?= $this->getNamespace('controller-implemented'); ?>\AbstractController {

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
        $this->form(new <?= $entity->class; ?>());
    }

    public function edit($id) {
        $entity = <?= $entity->class; ?>::findById($id);
        if (!$entity) {
            $this->response->notFound();
            return;
        }
        $this->form($entity);
    }

    protected function form(<?= $entity->class; ?> $<?= $entity->property; ?>) {
        if ($this->hasInput()) {
            $this->processInput($<?= $entity->property; ?>);
            if ($this->validate($<?= $entity->property; ?>)) {
                $<?= $entity->property; ?>->save();
                $this->response->redirect('/<?= $entity->route; ?>/edit/' . $<?= $entity->property; ?>->getId());
                return;
            }
        }

        $this->render('<?= $entity->getFileName(); ?>/form', [
            'entity' => $entity,
        ]);
    }

    protected function getDataTable(): \Rhino\DataTable\DataTable {
        return <?= $entity->class; ?>::getDataTable();
    }

    protected function processInput(<?= $entity->class; ?> $<?= $entity->property; ?>): void {
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string')
    || $attribute->is('text')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->string('<?= $attribute->property; ?>'));
<?php endif; ?>
<?php if ($attribute->is('date')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>(new \DateTimeImmutable($this->input->dateTime('<?= $attribute->property; ?>')));
<?php endif; ?>
<?php if ($attribute->is('bool')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->bool('<?= $attribute->property; ?>') ? true : false);
<?php endif; ?>
<?php endforeach; ?>
    }

    public function validate(): bool {
        return true;
    }
}
