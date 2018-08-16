<?php foreach ($entities as $entity): ?>
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->indexed): ?>
ALTER TABLE `<?= $entity->table; ?>` ADD INDEX `<?= $attribute->column; ?>` (`<?= $attribute->column; ?>`);
<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>
