DROP TABLE IF EXISTS <?= $entity->getTableName(); ?>;
CREATE TABLE IF NOT EXISTS <?= $entity->getTableName(); ?> (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
    <?= $attribute->getColumnName(); ?> INT NOT NULL,
<?php else: ?>
    <?= $attribute->getColumnName(); ?> VARCHAR(255) NOT NULL,
<?php endif; ?>
<?php endforeach; ?>
    created DATETIME NOT NULL,
    updated DATETIME NULL
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8
COLLATE = utf8_unicode_ci;
