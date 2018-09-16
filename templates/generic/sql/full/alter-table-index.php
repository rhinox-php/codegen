<?php foreach ($entities as $entity): ?>
<?php foreach ($entity->children('int') as $attribute): ?>
ALTER TABLE `<?= $entity->table; ?>` ADD INDEX `<?= $attribute->column; ?>` (`<?= $attribute->column; ?>`);
<?php endforeach; ?>
<?php endforeach; ?>
