ALTER TABLE <?= $entity->getTableName(); ?>

<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
ADD <?= $attribute->getColumnName(); ?> INT NULL AFTER <?= $previous; ?>;
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
ADD <?= $attribute->getColumnName(); ?> TINYINT(1) UNSIGNED NULL AFTER <?= $previous; ?>;
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
ADD <?= $attribute->getColumnName(); ?> MEDIUMTEXT NULL AFTER <?= $previous; ?>;
<?php else: ?>
ADD <?= $attribute->getColumnName(); ?> VARCHAR(255) NULL AFTER <?= $previous; ?>;
<?php endif; ?>
