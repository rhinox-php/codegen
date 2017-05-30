<?php foreach ($entities as $entity): ?>
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isIndexed()): ?>
ALTER TABLE `<?= $entity->getTableName(); ?>` ADD INDEX `<?= $attribute->getColumnName(); ?>` (`<?= $attribute->getColumnName(); ?>`);
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
