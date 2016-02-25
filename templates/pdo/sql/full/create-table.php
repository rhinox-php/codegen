DROP TABLE IF EXISTS <?= $entity->getTableName(); ?>;
<?php if ($entity->hasAuthentication()): ?>
DROP TABLE IF EXISTS <?= $entity->getTableName(); ?>_sessions;
<?php endif; ?>

CREATE TABLE IF NOT EXISTS <?= $entity->getTableName(); ?> (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
    <?= $attribute->getColumnName(); ?> INT NULL,
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
    <?= $attribute->getColumnName(); ?> TINYINT(1) UNSIGNED NULL,
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
    <?= $attribute->getColumnName(); ?> MEDIUMTEXT NULL,
<?php else: ?>
    <?= $attribute->getColumnName(); ?> VARCHAR(255) NULL,
<?php endif; ?>
<?php endforeach; ?>
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\OneToOne): ?>
<?php if ($entity == $relationship->getFrom()): ?>
    <?= $relationship->getTo()->getTableName(); ?>_id INT UNSIGNED NULL,
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
    created DATETIME NOT NULL,
    updated DATETIME NULL
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8
COLLATE = utf8_unicode_ci;

<?php if ($entity->hasAuthentication()): ?>
CREATE TABLE IF NOT EXISTS <?= $entity->getTableName(); ?>_sessions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    <?= $entity->getTableName(); ?>_id INT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    expire DATETIME NOT NULL,
    created DATETIME NOT NULL,
    updated DATETIME NULL,
    INDEX token (token),
    INDEX expire (expire)
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8
COLLATE = utf8_unicode_ci;
<?php endif; ?>