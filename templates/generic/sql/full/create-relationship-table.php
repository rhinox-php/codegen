CREATE TABLE IF NOT EXISTS <?= $relationship->getFrom()->getTableName(); ?>_<?= $relationship->getTo()->getTableName(); ?> (
    <?= $relationship->getFrom()->getTableName(); ?>_id INT UNSIGNED NOT NULL,
    <?= $relationship->getTo()->getTableName(); ?>_id INT UNSIGNED NOT NULL,
    created DATETIME NOT NULL,
    UNIQUE uid (<?= $relationship->getFrom()->getTableName(); ?>_id, <?= $relationship->getTo()->getTableName(); ?>_id)
)
ENGINE = InnoDB
DEFAULT CHARSET = <?= $codegen->getDatabaseCharset(); ?>

COLLATE = <?= $codegen->getDatabaseCollation(); ?>;
