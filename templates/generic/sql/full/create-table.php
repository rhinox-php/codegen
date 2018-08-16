DROP TABLE IF EXISTS `<?= $entity->table; ?>`;
<?php if ($entity->get('authentication')): ?>
DROP TABLE IF EXISTS `<?= $entity->table; ?>_sessions`;
<?php endif; ?>

CREATE TABLE IF NOT EXISTS `<?= $entity->table; ?>` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('int')): ?>
    `<?= $attribute->column; ?>` INT NULL,
<?php elseif ($attribute->is('bool')): ?>
    `<?= $attribute->column; ?>` TINYINT(1) UNSIGNED NULL,
<?php elseif ($attribute->is('text')): ?>
    `<?= $attribute->column; ?>` MEDIUMTEXT NULL,
<?php else: ?>
    `<?= $attribute->column; ?>` VARCHAR(255) NULL,
<?php endif; ?>
<?php endforeach; ?>
    `created` DATETIME NOT NULL,
    `updated` DATETIME NULL
)
ENGINE = InnoDB
DEFAULT CHARSET = <?= $codegen->getDatabaseCharset(); ?>

COLLATE = <?= $codegen->getDatabaseCollation(); ?>;

<?php if ($entity->get('authentication')): ?>
CREATE TABLE IF NOT EXISTS `<?= $entity->table; ?>_sessions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `<?= $entity->table; ?>_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expire` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    `updated` DATETIME NULL,
    INDEX `token` (`token`),
    INDEX `expire` (`expire`)
)
ENGINE = InnoDB
DEFAULT CHARSET = <?= $codegen->getDatabaseCharset(); ?>

COLLATE = <?= $codegen->getDatabaseCollation(); ?>;
<?php endif; ?>