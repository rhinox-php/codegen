<?php
namespace Rhino\Codegen\Template\Generic;
use Rhino\Codegen\Attribute;
use Rhino\Codegen\Entity;
use Rhino\Codegen\Database\Column\MySql as MySqlColumn;

class SqlMigrate extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\Interfaces\DatabaseMigrate {
    public function generate() {
    }

    public function iterateDatabaseMigrateSql(\PDO $pdo): iterable {
        foreach ($this->codegen->getEntities() as $entity) {
            if (!$this->codegen->db->tableExists($entity->getTableName())) {
                $this->codegen->log('Creating table', $entity->getTableName());
                yield "
                    CREATE TABLE IF NOT EXISTS `{$entity->getTableName()}` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `created` DATETIME NOT NULL,
                        `updated` DATETIME NULL
                    )
                    ENGINE = InnoDB
                    DEFAULT CHARSET = {$this->codegen->getDatabaseCharset()}
                    COLLATE = {$this->codegen->getDatabaseCollation()};
                ";
            }
            yield from $this->migrateColumns($entity);
        }
    }

    private function migrateColumns(Entity $entity): iterable {
        $previous = 'id';
        foreach ($entity->getAttributes() as $attribute) {
            $column = $this->codegen->db->getColumn($entity->getTableName(), $attribute->getColumnName());
            if (!$column->exists()) {
                if ($attribute instanceof Attribute\IntAttribute) {
                    $type = 'INT(11) SIGNED NULL';
                } elseif ($attribute instanceof Attribute\BoolAttribute) {
                    $type = 'TINYINT(1) UNSIGNED NULL';
                } elseif ($attribute instanceof Attribute\TextAttribute) {
                    $type = 'MEDIUMTEXT NULL';
                } elseif ($attribute instanceof Attribute\StringAttribute) {
                    $type = 'VARCHAR(255) NULL';
                } elseif ($attribute instanceof Attribute\DateAttribute) {
                    $type = 'DATE NULL';
                } elseif ($attribute instanceof Attribute\DateTimeAttribute) {
                    $type = 'DATETIME NULL';
                }
                $this->codegen->log('Creating column', $attribute->getColumnName(), $type, 'in', $entity->getTableName());
                yield "ALTER TABLE `{$entity->getTableName()}` ADD `{$attribute->getColumnName()}` $type AFTER `$previous`;";
            } else {
                yield from $this->migrateColumn($entity, $attribute, $column, $previous);
            }
            $previous = $attribute->getColumnName();
        }
    }

    private function migrateColumn(Entity $entity, Attribute $attribute, MySqlColumn $column, string $previous): iterable {
        if ($attribute instanceof Attribute\IntAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_INT) || !$column->isSize(11) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to INT(11) SIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getTableName());
                yield "ALTER TABLE `{$entity->getTableName()}` CHANGE `{$attribute->getColumnName()}` `{$attribute->getColumnName()}` INT(11) SIGNED NULL AFTER `$previous`;";
            }
        } elseif ($attribute instanceof Attribute\BoolAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_TINY_INT) || !$column->isSize(1) || $column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to TINYINT(1) UNSIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getTableName());
                yield "ALTER TABLE `{$entity->getTableName()}` CHANGE `{$attribute->getColumnName()}` `{$attribute->getColumnName()}` TINYINT(1) UNSIGNED NULL AFTER `$previous`;";
            }
        } elseif ($attribute instanceof Attribute\TextAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_MEDIUM_TEXT)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to MEDIUMTEXT from', $column->getType(), 'in', $entity->getTableName());
                yield "ALTER TABLE `{$entity->getTableName()}` CHANGE `{$attribute->getColumnName()}` `{$attribute->getColumnName()}` MEDIUMTEXT NULL AFTER `$previous`;";
            }
        } elseif ($attribute instanceof Attribute\StringAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(255)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to VARCHAR(255) from', $column->getType(), $column->getSize(), 'in', $entity->getTableName());
                yield "ALTER TABLE `{$entity->getTableName()}` CHANGE `{$attribute->getColumnName()}` `{$attribute->getColumnName()}` VARCHAR(255) NULL AFTER `$previous`;";
            }
        } elseif ($attribute instanceof Attribute\DateAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_DATE)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to DATE from', $column->getType(), $column->getSize(), 'in', $entity->getTableName());
                yield "ALTER TABLE `{$entity->getTableName()}` CHANGE `{$attribute->getColumnName()}` `{$attribute->getColumnName()}` DATE NULL AFTER `$previous`;";
            }
        } elseif ($attribute instanceof Attribute\DateTimeAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_DATE_TIME)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to DATETIME from', $column->getType(), $column->getSize(), 'in', $entity->getTableName());
                yield "ALTER TABLE `{$entity->getTableName()}` CHANGE `{$attribute->getColumnName()}` `{$attribute->getColumnName()}` DATETIME NULL AFTER `$previous`;";
            }
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
    }
}

