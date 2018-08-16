CREATE TABLE IF NOT EXISTS <?= $relationship->getFrom()table; ?>_<?= $relationship->getTo()table; ?> (
    <?= $relationship->getFrom()table; ?>_id INT UNSIGNED NOT NULL,
    <?= $relationship->getTo()table; ?>_id INT UNSIGNED NOT NULL,
    created DATETIME NOT NULL,
    UNIQUE uid (<?= $relationship->getFrom()table; ?>_id, <?= $relationship->getTo()table; ?>_id)
)
ENGINE = InnoDB
DEFAULT CHARSET = <?= $codegen->getDatabaseCharset(); ?>

COLLATE = <?= $codegen->getDatabaseCollation(); ?>;
