<?php $previous = null; ?>
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if (!$previous) { $previous = $attribute->getColumnName(); continue; } ?>
ALTER TABLE <?= $entity->getTableName(); ?>

<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
CHANGE <?= $attribute->getColumnName(); ?> <?= $attribute->getColumnName(); ?> INT NULL AFTER <?= $previous; ?>;
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
CHANGE <?= $attribute->getColumnName(); ?> <?= $attribute->getColumnName(); ?> TINYINT(1) UNSIGNED NULL AFTER <?= $previous; ?>;
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
CHANGE <?= $attribute->getColumnName(); ?> <?= $attribute->getColumnName(); ?> MEDIUMTEXT NULL AFTER <?= $previous; ?>;
<?php else: ?>
CHANGE <?= $attribute->getColumnName(); ?> <?= $attribute->getColumnName(); ?> VARCHAR(255) NULL AFTER <?= $previous; ?>;
<?php endif; ?>
<?php $previous = $attribute->getColumnName(); ?>

<?php endforeach; ?>
