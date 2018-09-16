<?php
namespace Rhino\Codegen\Template\Generic;

use Rhino\Codegen\Node;
use Rhino\Codegen\Database\Column\MySql as MySqlColumn;

class SqlMigrate extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\Interfaces\DatabaseMigrate
{
    public function generate()
    {
    }

    public function iterateDatabaseMigrateSql(\PDO $pdo, string $date): iterable
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            $path = $this->getFilePath('generic/sql/migrate', 'src/sql/up/' . $date . '.sql', [
                'date' => $date,
                'entity' => $entity,
            ]);
            if (!$this->codegen->db->tableExists($entity->table)) {
                $this->codegen->log('Creating table', $entity->table);
                yield $path => "
                    CREATE TABLE IF NOT EXISTS `{$entity->table}` (
                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `created_at` DATETIME NOT NULL,
                        `updated_at` DATETIME NULL
                    )
                    ENGINE = InnoDB
                    DEFAULT CHARSET = {$this->codegen->getDatabaseCharset()}
                    COLLATE = {$this->codegen->getDatabaseCollation()};
                ";
            }
            yield from $this->migrateColumns($entity, $path);
        }
    }

    private function migrateColumns(Node $entity, string $path): iterable
    {
        $previous = 'id';
        foreach ($entity->children as $attribute) {
            $column = $this->codegen->db->getColumn($entity->table, $attribute->column);
            if (!$column->exists()) {
                if ($attribute->is('int')) {
                    $type = 'INT(11) SIGNED NULL';
                } elseif ($attribute->is('decimal')) {
                    $type = 'DECIMAL(20, 4) NULL';
                } elseif ($attribute->is('bool')) {
                    $type = 'TINYINT(1) UNSIGNED NULL';
                } elseif ($attribute->is('text', 'json', 'html')) {
                    $type = 'MEDIUMTEXT NULL';
                } elseif ($attribute->is('string', 'password', 'enum')) {
                    $type = 'VARCHAR(255) NULL';
                } elseif ($attribute->is('date')) {
                    $type = 'DATE NULL';
                } elseif ($attribute->is('date-time')) {
                    $type = 'DATETIME NULL';
                } elseif ($attribute->is('uuid')) {
                    $type = 'BINARY(16) NULL';
                } else {
                    continue;
                }
                $this->codegen->log('Creating column', $attribute->column, $type, 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` ADD `{$attribute->column}` $type AFTER `$previous`;";
            } else {
                yield from $this->migrateColumn($entity, $attribute, $column, $previous, $path);
            }
            $previous = $attribute->column;
        }
    }

    private function migrateColumn(Node $entity, Node $attribute, MySqlColumn $column, string $previous, string $path): iterable
    {
        if ($attribute->is('int')) {
            if (!$column->isType(MySqlColumn::TYPE_INT) || !$column->isSize(11) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->column, 'to INT(11) SIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` INT(11) SIGNED NULL AFTER `$previous`;";
            }
        } elseif ($attribute->is('decimal')) {
            if (!$column->isType(MySqlColumn::TYPE_DECIMAL) || !$column->isDecimalSize(20, 4) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->column, 'to DECIMAL(20, 4) from', $column->getType(), $column->getDecimalSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` DECIMAL(20, 4) AFTER `$previous`;";
            }
        } elseif ($attribute->is('bool')) {
            if (!$column->isType(MySqlColumn::TYPE_TINY_INT) || !$column->isSize(1) || $column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->column, 'to TINYINT(1) UNSIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` TINYINT(1) UNSIGNED NULL AFTER `$previous`;";
            }
        } elseif ($attribute->is('text', 'json', 'html')) {
            if (!$column->isType(MySqlColumn::TYPE_MEDIUM_TEXT)) {
                $this->codegen->log('Changing column', $attribute->column, 'to MEDIUMTEXT from', $column->getType(), 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` MEDIUMTEXT NULL AFTER `$previous`;";
            }
        } elseif ($attribute->is('string', 'password', 'enum')) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(255)) {
                $this->codegen->log('Changing column', $attribute->column, 'to VARCHAR(255) from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` VARCHAR(255) NULL AFTER `$previous`;";
            }
        } elseif ($attribute->is('date')) {
            if (!$column->isType(MySqlColumn::TYPE_DATE)) {
                $this->codegen->log('Changing column', $attribute->column, 'to DATE from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` DATE NULL AFTER `$previous`;";
            }
        } elseif ($attribute->is('date-time')) {
            if (!$column->isType(MySqlColumn::TYPE_DATE_TIME)) {
                $this->codegen->log('Changing column', $attribute->column, 'to DATETIME from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` DATETIME NULL AFTER `$previous`;";
            }
        } elseif ($attribute->is('uuid')) {
            if (!$column->isType(MySqlColumn::TYPE_BINARY)) {
                $this->codegen->log('Changing column', $attribute->column, 'to BINARY(16) from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` BINARY(16) NULL AFTER `$previous`;";
            }
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
    }
}
