<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-serializer'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>;

class <?= $entity->getClassName(); ?>Serializer extends \Rhino\JsonApiList\JsonApiSerializer {

    public function iterateAttributes(\Rhino\JsonApiList\ModelInterface $entity): \Generator {
<?php $found = false; ?>
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if (!$attribute->isForeignKey()): ?>
<?php $found = true; ?>
        yield '<?= $attribute->getPropertyName(); ?>' => $entity->get<?= $attribute->getMethodName(); ?>();
<?php endif; ?>
<?php endforeach; ?>
<?php if (!$found): ?>
        yield from [];
<?php endif; ?>
    }

    public function iterateRelationships(\Rhino\JsonApiList\ModelInterface $entity): \Generator {
<?php $found = false; ?>
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
<?php $found = true; ?>
        yield '<?= $relationship->getPluralPropertyName(); ?>' => new <?= $relationship->getTo()->getClassName(); ?>Serializer($entity->get<?= $relationship->getPluralClassName(); ?>());
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
<?php $found = true; ?>
        yield '<?= $relationship->getPropertyName(); ?>' => new <?= $relationship->getTo()->getClassName(); ?>Serializer($entity->get<?= $relationship->getClassName(); ?>());
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php if (!$found): ?>
        yield from [];
<?php endif; ?>
    }

}
