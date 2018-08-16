<?php $previous = null; ?>
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
ALTER TABLE <?= $entity->table; ?>

<?php if ($attribute->is('int')): ?>
CHANGE <?= $attribute->column; ?> <?= $attribute->column; ?> INT NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute->is('bool')): ?>
CHANGE <?= $attribute->column; ?> <?= $attribute->column; ?> TINYINT(1) UNSIGNED NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute->is('text')): ?>
CHANGE <?= $attribute->column; ?> <?= $attribute->column; ?> MEDIUMTEXT NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute->is('date')): ?>
CHANGE <?= $attribute->column; ?> <?= $attribute->column; ?> DATE NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute->is('date-time')): ?>
CHANGE <?= $attribute->column; ?> <?= $attribute->column; ?> DATETIME NULL AFTER <?= $previous ?: 'id'; ?>;
<?php else: ?>
CHANGE <?= $attribute->column; ?> <?= $attribute->column; ?> VARCHAR(255) NULL AFTER <?= $previous ?: 'id'; ?>;
<?php endif; ?>
<?php $previous = $attribute->column; ?>

<?php endforeach; ?>
