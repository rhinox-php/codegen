<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-implemented'); ?>;

class <?= $entity->class; ?>Controller extends AbstractController {

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

    protected function form(<?= $entity->class; ?> $entity) {
        if ($this->hasInput()) {
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string')
    || $attribute->is('text')): ?>
            $entity->set<?= $attribute->method; ?>($this->input->string('<?= $attribute->property; ?>'));
<?php endif; ?>
<?php if ($attribute->is('date')): ?>
            $entity->set<?= $attribute->method; ?>(new \DateTimeImmutable($this->input->dateTime('<?= $attribute->property; ?>')));
<?php endif; ?>
<?php if ($attribute->is('bool')): ?>
            $entity->set<?= $attribute->method; ?>($this->input->bool('<?= $attribute->property; ?>') ? true : false);
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
