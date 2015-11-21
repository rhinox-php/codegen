DROP TABLE IF EXISTS <?= $entity->getTableName(); ?>;
CREATE TABLE IF NOT EXISTS <?= $entity->getTableName(); ?> (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
<?php foreach ($entity->getAttributes() as $attribute): ?>
    <?= $attribute->getColumnName(); ?> VARCHAR(30) NOT NULL,
<?php endforeach; ?>
    created DATETIME NOT NULL,
    updated DATETIME NULL
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8
COLLATE = utf8_unicode_ci;
