<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-serializer'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>;

class <?= $entity->getClassName(); ?>Serializer extends \Rhino\JsonApiList\JsonApiSerializer {

    public function iterateAttributes(\Rhino\JsonApiList\ModelInterface $entity) {
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if (!$attribute->isForeignKey()): ?>
        yield '<?= $attribute->getPropertyName(); ?>' => $entity->get<?= $attribute->getMethodName(); ?>();
<?php endif; ?>
<?php endforeach; ?>
    }

    public function iterateRelationships(\Rhino\JsonApiList\ModelInterface $entity) {
<?php $found = false; ?>
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
<?php $found = true; ?>
        yield '<?= $relationship->getPluralPropertyName(); ?>' => $entity->get<?= $relationship->getPluralClassName(); ?>();
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
<?php $found = true; ?>
        yield '<?= $relationship->getPropertyName(); ?>' => $entity->get<?= $relationship->getClassName(); ?>();
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php if (!$found): ?>
        return [];
<?php endif; ?>
    }

}
