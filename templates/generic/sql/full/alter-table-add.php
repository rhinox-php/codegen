<?php $previous = null; ?>
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
ALTER TABLE <?= $entity->table; ?>

<?php if ($attribute->is('int')): ?>
ADD <?= $attribute->column; ?> INT NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute->is('bool')): ?>
ADD <?= $attribute->column; ?> TINYINT(1) UNSIGNED NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute->is('text')): ?>
ADD <?= $attribute->column; ?> MEDIUMTEXT NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute->is('date')): ?>
ADD <?= $attribute->column; ?> DATE NULL AFTER <?= $previous ?: 'id'; ?>;
<?php elseif ($attribute->is('date-time')): ?>
ADD <?= $attribute->column; ?> DATETIME NULL AFTER <?= $previous ?: 'id'; ?>;
<?php else: ?>
ADD <?= $attribute->column; ?> VARCHAR(255) NULL AFTER <?= $previous ?: 'id'; ?>;
<?php endif; ?>
<?php $previous = $attribute->column; ?>

<?php endforeach; ?>
