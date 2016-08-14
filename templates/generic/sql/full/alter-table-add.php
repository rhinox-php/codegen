<?php $previous = null; ?>
<?php foreach ($entity->getAttributes() as $attribute): ?>
ALTER TABLE <?= $entity->getTableName(); ?>

<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
ADD <?= $attribute->getColumnName(); ?> INT NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
ADD <?= $attribute->getColumnName(); ?> TINYINT(1) UNSIGNED NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
ADD <?= $attribute->getColumnName(); ?> MEDIUMTEXT NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
ADD <?= $attribute->getColumnName(); ?> DATE NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\DateTimeAttribute): ?>
ADD <?= $attribute->getColumnName(); ?> DATETIME NULL AFTER <?= $previous ?: 'id'; ?>;
<?php else: ?>
ADD <?= $attribute->getColumnName(); ?> VARCHAR(255) NULL AFTER <?= $previous ?: 'id'; ?>;
<?php endif; ?>
<?php $previous = $attribute->getColumnName(); ?>

<?php endforeach; ?>
