CREATE DATABASE IF NOT EXISTS <?= $codegen->getDatabaseName(); ?>

DEFAULT CHARSET = <?= $codegen->getDatabaseCharset(); ?>

COLLATE = <?= $codegen->getDatabaseCollation(); ?>;
