<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-api-generated'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>;
use <?= $this->getNamespace('model-serializer'); ?>\<?= $entity->getClassName(); ?>Serializer;

class <?= $entity->getClassName(); ?>ApiController extends AbstractController {

    public function index() {
        $list = new \Rhino\JsonApiList\JsonApiList(<?= $entity->getClassName(); ?>::class, $this->input);
        $list->setSortColumns([
        ]);
        $list->setSearchColumns([
        ]);
        $list->process();
        $this->response->json(new <?= $entity->getClassName(); ?>Serializer($list->getResults(), $list->getMeta()));
    }

    public function get($id) {
        $<?= $entity->getPropertyName(); ?> = <?= $entity->getClassName(); ?>::findById($id);
        if (!$<?= $entity->getPropertyName(); ?>) {
            $this->response->notFound();
            return;
        }
        $this->response->json(new <?= $entity->getClassName(); ?>Serializer($<?= $entity->getPropertyName(); ?>));
    }

    public function create() {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String', 'Text'])): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->string('data.attributes.<?= $attribute->getPropertyName(); ?>'));
<?php elseif ($attribute->is(['Date'])): ?>
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($this->input->date('data.attributes.<?= $attribute->getPropertyName(); ?>'));
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

        $<?= $entity->getPropertyName(); ?>->save();

        $this->response->json(new <?= $entity->getClassName(); ?>Serializer($<?= $entity->getPropertyName(); ?>));
    }

    public function update($id) {
        $<?= $entity->getPropertyName(); ?> = <?= $entity->getClassName(); ?>::findById($id);
        if (!$<?= $entity->getPropertyName(); ?>) {
            $this->response->notFound();
            return;
        }
        $this->response->json(new <?= $entity->getClassName(); ?>Serializer($<?= $entity->getPropertyName(); ?>));
    }

    public function delete($id) {
        $<?= $entity->getPropertyName(); ?> = <?= $entity->getClassName(); ?>::findById($id);
        if (!$<?= $entity->getPropertyName(); ?>) {
            $this->response->notFound();
            return;
        }
        $this->response->json(new <?= $entity->getClassName(); ?>Serializer($<?= $entity->getPropertyName(); ?>));
    }

}
