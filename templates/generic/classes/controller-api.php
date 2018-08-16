<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-api-generated'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>;
use <?= $this->getNamespace('model-serializer'); ?>\<?= $entity->class; ?>Serializer;

class <?= $entity->class; ?>ApiController extends AbstractController {

    public function index() {
        $list = new \Rhino\JsonApiList\JsonApiList(<?= $entity->class; ?>::class, $this->input);
        $list->setSortColumns([
        ]);
        $list->setSearchColumns([
        ]);
        $list->process();
        $this->response->json(new <?= $entity->class; ?>Serializer($list->getResults(), $list->getMeta()));
    }

    public function get($id) {
        $<?= $entity->property; ?> = <?= $entity->class; ?>::findById($id);
        if (!$<?= $entity->property; ?>) {
            $this->response->notFound();
            return;
        }
        $this->response->json(new <?= $entity->class; ?>Serializer($<?= $entity->property; ?>));
    }

    public function create() {
        $<?= $entity->property; ?> = new <?= $entity->class; ?>();
        $this->updateAttributes($<?= $entity->property; ?>);
        $<?= $entity->property; ?>->save();

        $this->response->json(new <?= $entity->class; ?>Serializer($<?= $entity->property; ?>));
    }

    public function update($id) {
        $<?= $entity->property; ?> = <?= $entity->class; ?>::findById($id);
        if (!$<?= $entity->property; ?>) {
            $this->response->notFound();
            return;
        }
        $this->updateAttributes($<?= $entity->property; ?>);
        $<?= $entity->property; ?>->save();
        $this->response->json(new <?= $entity->class; ?>Serializer($<?= $entity->property; ?>));
    }

    public function delete($id) {
        $<?= $entity->property; ?> = <?= $entity->class; ?>::findById($id);
        if (!$<?= $entity->property; ?>) {
            $this->response->notFound();
            return;
        }
        $<?= $entity->property; ?>->delete();
        $this->response->json(new <?= $entity->class; ?>Serializer($<?= $entity->property; ?>));
    }

    private function updateAttributes(<?= $entity->class; ?> $<?= $entity->property; ?>) {
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->string('data.attributes.<?= $attribute->property; ?>'));
<?php elseif ($attribute->is('date')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->dateTime('data.attributes.<?= $attribute->property; ?>'));
<?php elseif ($attribute->is('date-time')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->dateTime('data.attributes.<?= $attribute->property; ?>'));
<?php elseif ($attribute->is('bool')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->bool('data.attributes.<?= $attribute->property; ?>'));
<?php elseif ($attribute->is('int')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->int('data.attributes.<?= $attribute->property; ?>'));
<?php elseif ($attribute->is('decimal')): ?>
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($this->input->decimal('data.attributes.<?= $attribute->property; ?>'));
<?php endif; ?>
<?php endforeach; ?>
    }

}
