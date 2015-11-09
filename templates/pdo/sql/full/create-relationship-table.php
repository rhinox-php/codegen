CREATE TABLE IF NOT EXISTS <?= $entity->getTableName(); ?>_<?= $relationship->getTo()->getTableName(); ?> (
    <?= $entity->getTableName(); ?>_id INT UNSIGNED NOT NULL,
    <?= $relationship->getTo()->getTableName(); ?>_id INT UNSIGNED NOT NULL,
    created DATETIME NOT NULL,
    UNIQUE uid (<?= $entity->getTableName(); ?>_id, <?= $relationship->getTo()->getTableName(); ?>_id)
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8
COLLATE = utf8_unicode_ci;
