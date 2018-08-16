<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-serializer'); ?>;
use <?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>;

class <?= $entity->class; ?>Serializer extends \Rhino\JsonApiList\JsonApiSerializer {

    public function iterateAttributes(\Rhino\JsonApiList\ModelInterface $entity): \Generator {
<?php $found = false; ?>
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if (!$attribute->isForeignKey()): ?>
<?php $found = true; ?>
        yield '<?= $attribute->property; ?>' => $entity->get<?= $attribute->method; ?>();
<?php endif; ?>
<?php endforeach; ?>
<?php if (!$found): ?>
        yield from [];
<?php endif; ?>
    }

    public function iterateRelationships(\Rhino\JsonApiList\ModelInterface $entity): \Generator {
<?php $found = false; ?>
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
<?php $found = true; ?>
        yield '<?= $relationship->pluralProperty; ?>' => new <?= $relationship->getTo()->class; ?>Serializer($entity->get<?= $relationship->pluralClass; ?>());
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
<?php $found = true; ?>
        yield '<?= $relationship->property; ?>' => new <?= $relationship->getTo()->class; ?>Serializer($entity->get<?= $relationship->class; ?>());
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
<?php if (!$found): ?>
        yield from [];
<?php endif; ?>
    }

}
