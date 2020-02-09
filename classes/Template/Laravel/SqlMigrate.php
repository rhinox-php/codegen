<?php

namespace Rhino\Codegen\Template\Laravel;

use Rhino\Codegen\Attribute;
use Rhino\Codegen\Entity;
use Rhino\Codegen\Node;
use Rhino\Codegen\Database\Column\MySql as MySqlColumn;

class SqlMigrate extends \Rhino\Codegen\Template\Laravel implements \Rhino\Codegen\Template\Interfaces\DatabaseMigrate
{
    private $columnMapper = null;

    public function generate()
    { }

    public function iterateDatabaseMigrateSql(\PDO $pdo, string $date): iterable
    {
        if (!$this->columnMapper) {
            throw new \Exception('SQL migrate requires a column mapper');
        }
        $date = date('Y_m_d_His');
        $migrationNumber = count(glob($this->codegen->getPath(dirname($this->getPath('laravel/sql/migrate')) . '/*.php')));
        foreach ($this->codegen->node->children('entity') as $entity) {
            $path = $this->getFilePath('laravel/sql/migrate', 'database/migrations/{{ $date }}_{{ $entity->table }}_{{ $migrationNumber }}.php', [
                'date' => $date,
                'entity' => $entity,
                'migrationNumber' => $migrationNumber,
            ]);
            $createMigration = '';
            $dropMigration = '';
            if (!$this->codegen->db->tableExists($entity->table)) {
                $this->codegen->log('Creating table', $entity->table);
                $createMigration = "
                            Schema::create('{$entity->table}', function (Blueprint \$table) {
                                \$table->increments('id');
                                \$table->timestamps();
                                \$table->softDeletes();
                            });
                ";
                $dropMigration = "
                            Schema::drop('{$entity->table}');
                ";
            }
            $columnMigrations = [];
            $reverseColumnMigrations = [];
            $postMigrations = [];
            $reversePostMigrations = [];
            foreach ($this->migrateColumns($entity, $path) as $migrations) {
                while (count($migrations) < 4) {
                    $migrations[] = null;
                }
                [$up, $down, $postUp, $postDown] = $migrations;
                if ($up) {
                    $columnMigrations[] = $up;
                }
                if ($down) {
                    $reverseColumnMigrations[] = $down;
                }
                if ($postUp) {
                    $postMigrations[] = $postUp;
                }
                if ($postDown) {
                    $reversePostMigrations[] = $postDown;
                }
            }
            if (!empty($columnMigrations) || !empty($reverseColumnMigrations)) {
                $columnMigrations = implode("\n" . str_repeat(' ', 32), $columnMigrations);
                $reverseColumnMigrations = implode("\n" . str_repeat(' ', 32), $reverseColumnMigrations);
                $postMigrations = implode("\n" . str_repeat(' ', 32), $postMigrations);
                $reversePostMigrations = implode("\n" . str_repeat(' ', 32), $reversePostMigrations);
                yield $path => "
                    <?php
                    use Illuminate\Database\Schema\Blueprint;

                    class {$entity->pluralClass}{$migrationNumber} extends \Illuminate\Database\Migrations\Migration
                    {
                        public function up()
                        {
                            $createMigration
                            Schema::table('{$entity->table}', function (Blueprint \$table) {
                                $columnMigrations
                            });
                            $postMigrations
                        }

                        public function down()
                        {
                            Schema::table('{$entity->table}', function (Blueprint \$table) {
                                $reverseColumnMigrations
                            });
                            $reversePostMigrations
                            $dropMigration
                        }
                    }
                ";
                $migrationNumber++;
            }
        }
    }

    private function migrateColumns(Node $entity, string $path): iterable
    {
        $previous = 'id';

        foreach ($entity->children as $attribute) {
            $columnSchema = ($this->columnMapper)($attribute);
            $this->codegen->log('Column schema', $attribute->column, $columnSchema);
            if (!$columnSchema) {
                continue;
            }
            $column = $this->codegen->db->getColumn($entity->table, $attribute->column);
            if (!$column->exists()) {
                $this->codegen->log('Creating column', $attribute->column, 'in', $entity->table);
                $nullable = '';
                if (isset($columnSchema['nullable']) && $columnSchema['nullable']) {
                    $nullable = "->nullable()";
                }
                $unique = '';
                if (isset($columnSchema['unique']) && $columnSchema['unique']) {
                    $uniqueName = $attribute->column;
                    $unique = "->unique('$uniqueName')";
                }
                $indexed = '';
                if (!$unique && isset($columnSchema['indexed']) && $columnSchema['indexed']) {
                    $indexName = $attribute->column;
                    $indexed = "->index('$indexName')";
                }
                $size = '';
                if (isset($columnSchema['size']) && $columnSchema['size']) {
                    $size = ", {$columnSchema['size']}";
                }
                yield [
                    "\$table->{$columnSchema['type']}('{$attribute->column}'$size){$nullable}{$indexed}{$unique}->after('{$previous}');",
                    "\$table->dropColumn('{$attribute->column}');",
                ];
                // if ($attribute->is('int')) {
                // } elseif ($attribute->is('decimal')) {
                //     yield [
                //         "\$table->decimal('{$attribute->column}', 20, 4)->nullable()->after('{$previous}');",
                //         "\$table->dropColumn('{$attribute->column}');",
                //     ];
                // } elseif ($attribute->is('bool')) {
                //     yield [
                //         "\$table->boolean('{$attribute->column}')->nullable()->after('{$previous}');",
                //         "\$table->dropColumn('{$attribute->column}');",
                //     ];
                // } elseif ($attribute->is('text', 'json')) {
                //     yield [
                //         "\$table->mediumText('{$attribute->column}')->nullable()->after('{$previous}');",
                //         "\$table->dropColumn('{$attribute->column}');",
                //     ];
                // } elseif ($attribute->is('string')) {
                //     yield [
                //         "\$table->string('{$attribute->column}')->nullable()->after('{$previous}');",
                //         "\$table->dropColumn('{$attribute->column}');",
                //     ];
                // } elseif ($attribute->is('date')) {
                //     yield [
                //         "\$table->date('{$attribute->column}')->nullable()->after('{$previous}');",
                //         "\$table->dropColumn('{$attribute->column}');",
                //     ];
                // } elseif ($attribute->is('date-time')) {
                //     yield [
                //         "\$table->dateTime('{$attribute->column}')->nullable()->after('{$previous}');",
                //         "\$table->dropColumn('{$attribute->column}');",
                //     ];
                // } elseif ($attribute->is('password')) {
                //     yield [
                //         "\$table->string('{$attribute->column}')->nullable()->after('{$previous}');",
                //         "\$table->dropColumn('{$attribute->column}');",
                //     ];
                // } elseif ($attribute->is('uuid')) {
                //     $null = $columnSchema['nullable'] ? 'NULL' : 'NOT NULL';
                //     yield [
                //         null,
                //         "\$table->dropColumn('{$attribute->column}');",
                //         "\DB::statement('ALTER TABLE `{$entity->table}` ADD `{$attribute->column}` BINARY(16) $null AFTER `{$previous}`;');",
                //     ];
                // } else {
                //     $this->codegen->log('Unknown column type', $attribute->column, get_class($attribute));
                // }
            } else {
                yield from $this->migrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema);
            }
            $previous = $attribute->column;
        }
    }

    private function migrateColumn(Node $entity, Node $attribute, MySqlColumn $column, string $previous, string $path, array $columnSchema): iterable
    {
        $nullable = '';
        if (isset($columnSchema['nullable']) && $columnSchema['nullable']) {
            $isNullable = true;
            $nullable = "->nullable(true)";
        } else {
            $isNullable = false;
            $nullable = "->nullable(false)";
        }
        $unique = '';
        if (isset($columnSchema['unique']) && $columnSchema['unique']) {
            $uniqueName = $attribute->column;
            $unique = "->unique('$uniqueName')";
        }
        $indexed = '';
        if (!$unique && isset($columnSchema['indexed']) && $columnSchema['indexed']) {
            $indexName = $attribute->column;
            $indexed = "->index('$indexName')";
        }
        $size = '';
        if (isset($columnSchema['size']) && $columnSchema['size']) {
            $size = ", {$columnSchema['size']}";
        }
        if ($attribute->is('int')) {
            if (!$column->isType(MySqlColumn::TYPE_INT) || !$column->isSize(11) || !$column->isSigned() || $column->isNullable() != $columnSchema['nullable']) {
                $this->codegen->log('Changing column', $attribute->column, 'to INT(11) SIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->table);
                yield [
                    "\$table->integer('{$attribute->column}'){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } elseif ($attribute->is('decimal')) {
            if (!$column->isType(MySqlColumn::TYPE_DECIMAL) || !$column->isDecimalSize(20, 4) || !$column->isSigned() || $column->isNullable() != $columnSchema['nullable']) {
                $this->codegen->log('Changing column', $attribute->column, 'to DECIMAL(20, 4) from', $column->getType(), $column->getDecimalSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->table);
                yield [
                    "\$table->decimal('{$attribute->column}', 20, 4){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } elseif ($attribute->is('bool')) {
            if (!$column->isType(MySqlColumn::TYPE_TINY_INT) || !$column->isSize(1) || !$column->isSigned() || ($isNullable != $column->isNullable())) {
                $this->codegen->log('Changing column', $attribute->column, 'to TINYINT(1) UNSIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->table);
                yield [
                    "\$table->boolean('{$attribute->column}'){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } elseif ($attribute->is('text', 'json')) {
            if (!$column->isType(MySqlColumn::TYPE_MEDIUM_TEXT) || $column->isNullable() != $columnSchema['nullable']) {
                $this->codegen->log('Changing column', $attribute->column, 'to MEDIUMTEXT from', $column->getType(), 'in', $entity->table);
                yield [
                    "\$table->mediumText('{$attribute->column}'){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } elseif ($attribute->is('string')) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(191) || $column->isNullable() != $columnSchema['nullable']) {
                $this->codegen->log('Changing column', $attribute->column, 'to VARCHAR(191) from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield [
                    "\$table->string('{$attribute->column}'){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } elseif ($attribute->is('date')) {
            if (!$column->isType(MySqlColumn::TYPE_DATE) || $column->isNullable() != $columnSchema['nullable']) {
                $this->codegen->log('Changing column', $attribute->column, 'to DATE from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield [
                    "\$table->date('{$attribute->column}'){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } elseif ($attribute->is('date-time')) {
            if (!$column->isType(MySqlColumn::TYPE_DATE_TIME) || $column->isNullable() != $columnSchema['nullable']) {
                $this->codegen->log('Changing column', $attribute->column, 'to DATETIME from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield [
                    "\$table->dateTime('{$attribute->column}'){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } elseif ($attribute->is('password')) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(191) || $column->isNullable() != $columnSchema['nullable']) {
                $this->codegen->log('Changing column', $attribute->column, 'to VARCHAR(191) from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield [
                    "\$table->string('{$attribute->column}'){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } elseif ($attribute->is('uuid')) {
            if (!$column->isType(MySqlColumn::TYPE_BINARY) || !$column->isSize(16) || $column->isNullable() != $columnSchema['nullable']) {
                $this->codegen->log('Changing column', $attribute->column, 'to BINARY(16) from', $column->getType(), $column->getSize(), 'in', $entity->table);
                yield [
                    "\$table->uuid('{$attribute->column}'){$nullable}->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column, $previous, $path, $columnSchema),
                ];
            }
        } else {
            $this->codegen->log('Unknown column type', $attribute->column, get_class($attribute));
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
    }

    private function reverseMigrateColumn(Node $entity, Node $attribute, MySqlColumn $column, string $previous, string $path, array $columnSchema): ?string
    {
        if ($column->isType(MySqlColumn::TYPE_INT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to INT(11) SIGNED in', $entity->table);
            return "\$table->integer('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DECIMAL)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to DECIMAL(20, 4) in', $entity->table);
            return "\$table->decimal('{$attribute->column}', 20, 4)->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_TINY_INT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to TINYINT(1) SIGNED in', $entity->table);
            return "\$table->boolean('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_MEDIUM_TEXT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to MEDIUMTEXT in', $entity->table);
            return "\$table->mediumText('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_VARCHAR)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to VARCHAR(191) in', $entity->table);
            return "\$table->string('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DATE)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to DATE in', $entity->table);
            return "\$table->date('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DATE_TIME)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to DATETIME in', $entity->table);
            return "\$table->dateTime('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_VARCHAR)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to VARCHAR(191) in', $entity->table);
            return "\$table->string('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_BINARY)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to BINARY(16) in', $entity->table);
            $null = $columnSchema['nullable'] ? 'NULL' : 'NOT NULL';
            return "\DB::statement('ALTER TABLE `{$entity->table}` CHANGE `{$attribute->column}` `{$attribute->column}` BINARY(16) $null;');";
        } else {
            $this->codegen->log('Unknown column type', $attribute->column, get_class($attribute));
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
        return null;
    }

    public function setColumnMapper($columnMapper)
    {
        $this->columnMapper = $columnMapper;
        return $this;
    }
}
