<?php

namespace Rhino\Codegen\Template\Generic;

use Rhino\Codegen\Database\Column\MySql as MySqlColumn;
use Rhino\Codegen\Node;

class SqlMigrate extends \Rhino\Codegen\Template\Generic implements \Rhino\Codegen\Template\Interfaces\DatabaseMigrate
{
    private $columnMapper = null;

    public function generate()
    {
    }

    public function iterateDatabaseMigrateSql(\PDO $pdo, string $date): iterable
    {
        if (!$this->columnMapper) {
            throw new \Exception('SQL migrate requires a column mapper');
        }
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
                        `updated_at` DATETIME NULL,
                        `deleted_at` DATETIME NULL
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
            $columnSchema = ($this->columnMapper)($attribute);
            if (!$columnSchema) {
                continue;
            }
            $columnName = $columnSchema['name'] ?? $attribute->column;
            $column = $this->codegen->db->getColumn($entity->table, $columnName);
            if (!$column->exists()) {
                $type = $this->getColumnTypeSql($columnSchema);
                if (!$type) {
                    continue;
                }
                $this->codegen->log('Creating column', $columnName, $type, 'in', $entity->table);
                yield $path => "ALTER TABLE `{$entity->table}` ADD `{$columnName}` $type AFTER `$previous`;";
            } else {
                yield from $this->migrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema);
            }
            $previous = $columnName;
        }
    }

    private function migrateColumn(Node $entity, Node $attribute, MySqlColumn $column, string $previous, string $path, array $columnSchema): iterable
    {
        $columnName = $columnSchema['name'] ?? $attribute->column;
        $alter = false;
        if (!$column->isType($columnSchema['type'])) {
            $alter = true;
            $this->codegen->log('Change table column type', $entity->table, $columnName, $columnSchema['type'], $column->getType());
        }
        if (isset($columnSchema['size'])) {
            if ($column->getSize() !== null && !$column->isSize($columnSchema['size'])) {
                $alter = true;
                $this->codegen->log('Change table column size', $entity->table, $columnName, $columnSchema['size'], $column->getSize());
            }
        }
        if (isset($columnSchema['signed'])) {
            if ($column->isSigned() != $columnSchema['signed']) {
                $alter = true;
                $this->codegen->log('Change table column sign', $entity->table, $columnName, $column->isSigned() ? 'signed' : 'unsigned', 'to', $columnSchema['signed'] ? 'signed' : 'unsigned');
            }
        }
        if (isset($columnSchema['nullable'])) {
            if ($column->isNullable() != $columnSchema['nullable']) {
                $alter = true;
                $this->codegen->log('Change table column null', $entity->table, $columnName, $columnSchema['nullable'], $column->isNullable());
            }
        }
        $type = $this->getColumnTypeSql($columnSchema);
        $columnName = $columnSchema['name'] ?? $attribute->column;
        if ($alter && $type) {
            yield $path => "ALTER TABLE `{$entity->table}` CHANGE `{$columnName}` `{$columnName}` $type AFTER `$previous`;";
        }

        $indexed = $columnSchema['indexed'] ?? false;
        if ($indexed && !$column->isIndexed()) {
            yield $path => "ALTER TABLE `{$entity->table}` ADD INDEX `{$columnName}` (`{$columnName}`);";
        }
        // @todo check collation
    }

    private function getColumnTypeSql(array $columnSchema): ?string
    {
        $type = [];
        $type[] = $columnSchema['type'];
        if (isset($columnSchema['size'])) {
            $type[] = '(' . $columnSchema['size'] . ')';
        }
        if (isset($columnSchema['signed'])) {
            $type[] = $columnSchema['signed'] ? 'SIGNED' : 'UNSIGNED';
        }
        if (isset($columnSchema['nullable'])) {
            $type[] = $columnSchema['nullable'] ? 'NULL' : 'NOT NULL';
        }
        if (empty($type)) {
            return null;
        }
        $type = implode(' ', $type);
        return $type;
    }

    public function setColumnMapper($columnMapper)
    {
        $this->columnMapper = $columnMapper;
        return $this;
    }
}
