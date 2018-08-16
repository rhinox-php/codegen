ALTER TABLE <?= $entity->table; ?>

<?php if ($attribute->is('int')): ?>
ADD <?= $attribute->column; ?> INT NULL AFTER <?= $previous; ?>;
<?php elseif ($attribute->is('bool')): ?>
ADD <?= $attribute->column; ?> TINYINT(1) UNSIGNED NULL AFTER <?= $previous; ?>;
<?php elseif ($attribute->is('text')): ?>
ADD <?= $attribute->column; ?> MEDIUMTEXT NULL AFTER <?= $previous; ?>;
<?php else: ?>
ADD <?= $attribute->column; ?> VARCHAR(255) NULL AFTER <?= $previous; ?>;
<?php endif; ?>
