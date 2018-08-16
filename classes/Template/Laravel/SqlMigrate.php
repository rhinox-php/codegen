<?php
namespace Rhino\Codegen\Template\Laravel;

use Rhino\Codegen\Attribute;
use Rhino\Codegen\Entity;
use Rhino\Codegen\Database\Column\MySql as MySqlColumn;

class SqlMigrate extends \Rhino\Codegen\Template\Laravel implements \Rhino\Codegen\Template\Interfaces\DatabaseMigrate
{
    public function generate()
    {
    }

    public function iterateDatabaseMigrateSql(\PDO $pdo, string $date): iterable
    {
        $date = date('Y_m_d_His');
        $migrationNumber = count(glob($this->codegen->getPath('database/migrations/*.php')));
        foreach ($this->codegen->node->children('entity') as $entity) {
            $path = $this->getFilePath('generic/sql/migrate', 'database/migrations/' . $date . '_' . $entity->table . '_' . $migrationNumber . '.php', [
                'date' => $date,
                'entity' => $entity,
            ]);
            $createMigration = '';
            $dropMigration = '';
            if (!$this->codegen->db->tableExists($entity->getPluralTableName())) {
                $this->codegen->log('Creating table', $entity->getPluralTableName());
                $createMigration = "
                            Schema::create('{$entity->getPluralTableName()}', function (Blueprint \$table) {
                                \$table->increments('id');
                                \$table->dateTime('created_at')->nullable()->default(null);
                                \$table->dateTime('updated_at')->nullable()->default(null);
                            });
                ";
                $dropMigration = "
                            Schema::drop('{$entity->getPluralTableName()}');
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

                    class {$entity->class}{$migrationNumber} extends \Illuminate\Database\Migrations\Migration
                    {
                        public function up()
                        {
                            $createMigration
                            Schema::table('{$entity->getPluralTableName()}', function (Blueprint \$table) {
                                $columnMigrations
                            });
                            $postMigrations
                        }

                        public function down()
                        {
                            Schema::table('{$entity->getPluralTableName()}', function (Blueprint \$table) {
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

    private function migrateColumns(Entity $entity, string $path): iterable
    {
        $previous = 'id';
        foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute) {
            $column = $this->codegen->db->getColumn($entity->getPluralTableName(), $attribute->column);
            if (!$column->exists()) {
                $this->codegen->log('Creating column', $attribute->column, 'in', $entity->getPluralTableName());
                if ($attribute->is('int')) {
                    yield [
                        "\$table->integer('{$attribute->column}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->column}');",
                    ];
                } elseif ($attribute->is('decimal')) {
                    yield [
                        "\$table->decimal('{$attribute->column}', 10, 2)->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->column}');",
                    ];
                } elseif ($attribute->is('bool')) {
                    yield [
                        "\$table->bool('{$attribute->column}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->column}');",
                    ];
                } elseif ($attribute->is('text', 'json')) {
                    yield [
                        "\$table->mediumText('{$attribute->column}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->column}');",
                    ];
                } elseif ($attribute->is('string')) {
                    yield [
                        "\$table->string('{$attribute->column}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->column}');",
                    ];
                } elseif ($attribute->is('date')) {
                    yield [
                        "\$table->date('{$attribute->column}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->column}');",
                    ];
                } elseif ($attribute->is('date-time')) {
                    yield [
                        "\$table->dateTime('{$attribute->column}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->column}');",
                    ];
                } elseif ($attribute->is('password')) {
                    yield [
                        "\$table->string('{$attribute->column}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->column}');",
                    ];
                } elseif ($attribute->is('uuid')) {
                    $null = $attribute->nullable ? 'NULL' : 'NOT NULL';
                    yield [
                        null,
                        "\$table->dropColumn('{$attribute->column}');",
                        "\DB::statement('ALTER TABLE `{$entity->getPluralTableName()}` ADD `{$attribute->column}` BINARY(16) $null AFTER `{$previous}`;');",
                    ];
                } else {
                    $this->codegen->log('Unknown column type', $attribute->column, get_class($attribute));
                }
            } else {
                yield from $this->migrateColumn($entity, $attribute, $column, $previous, $path);
            }
            $previous = $attribute->column;
        }
    }

    private function migrateColumn(Entity $entity, Attribute $attribute, MySqlColumn $column, string $previous, string $path): iterable
    {
        if ($attribute->is('int')) {
            if (!$column->isType(MySqlColumn::TYPE_INT) || !$column->isSize(11) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->column, 'to INT(11) SIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield [
                    "\$table->integer('{$attribute->column}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->is('decimal')) {
            if (!$column->isType(MySqlColumn::TYPE_DECIMAL) || !$column->isDecimalSize(10, 2) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->column, 'to DECIMAL(10, 2) from', $column->getType(), $column->getDecimalSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield [
                    "\$table->decimal('{$attribute->column}', 10, 2)->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->is('bool')) {
            if (!$column->isType(MySqlColumn::TYPE_TINY_INT) || !$column->isSize(1) || $column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->column, 'to TINYINT(1) UNSIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield [
                    "\$table->bool('{$attribute->column}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->is('text', 'json')) {
            if (!$column->isType(MySqlColumn::TYPE_MEDIUM_TEXT)) {
                $this->codegen->log('Changing column', $attribute->column, 'to MEDIUMTEXT from', $column->getType(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->mediumText('{$attribute->column}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->is('string')) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(255)) {
                $this->codegen->log('Changing column', $attribute->column, 'to VARCHAR(255) from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->string('{$attribute->column}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->is('date')) {
            if (!$column->isType(MySqlColumn::TYPE_DATE)) {
                $this->codegen->log('Changing column', $attribute->column, 'to DATE from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->date('{$attribute->column}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->is('date-time')) {
            if (!$column->isType(MySqlColumn::TYPE_DATE_TIME)) {
                $this->codegen->log('Changing column', $attribute->column, 'to DATETIME from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->dateTime('{$attribute->column}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->is('password')) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(255)) {
                $this->codegen->log('Changing column', $attribute->column, 'to VARCHAR(255) from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->string('{$attribute->column}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->is('uuid')) {
            if (!$column->isType(MySqlColumn::TYPE_BINARY) || !$column->isSize(16) || $column->nullable != $attribute->nullable) {
                $this->codegen->log('Changing column', $attribute->column, 'to BINARY(16) from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                $null = $attribute->nullable ? 'NULL' : 'NOT NULL';
                yield [
                    null,
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                    "\DB::statement('ALTER TABLE `{$entity->getPluralTableName()}` CHANGE `{$attribute->column}` `{$attribute->column}` BINARY(16) $null;');",
                ];
            }
        } else {
            $this->codegen->log('Unknown column type', $attribute->column, get_class($attribute));
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
    }

    private function reverseMigrateColumn(Entity $entity, Attribute $attribute, MySqlColumn $column): ?string
    {
        if ($column->isType(MySqlColumn::TYPE_INT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to INT(11) SIGNED in', $entity->getPluralTableName());
            return "\$table->integer('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DECIMAL)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to DECIMAL(10, 2) in', $entity->getPluralTableName());
            return "\$table->decimal('{$attribute->column}', 10, 2)->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_TINY_INT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to TINYINT(1) UNSIGNED in', $entity->getPluralTableName());
            return "\$table->bool('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_MEDIUM_TEXT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to MEDIUMTEXT in', $entity->getPluralTableName());
            return "\$table->mediumText('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_VARCHAR)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to VARCHAR(255) in', $entity->getPluralTableName());
            return "\$table->string('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DATE)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to DATE in', $entity->getPluralTableName());
            return "\$table->date('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DATE_TIME)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to DATETIME in', $entity->getPluralTableName());
            return "\$table->dateTime('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_VARCHAR)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to VARCHAR(255) in', $entity->getPluralTableName());
            return "\$table->string('{$attribute->column}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_BINARY)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->column, 'to BINARY(16) in', $entity->getPluralTableName());
            $null = $attribute->nullable ? 'NULL' : 'NOT NULL';
            return "\DB::statement('ALTER TABLE `{$entity->getPluralTableName()}` CHANGE `{$attribute->column}` `{$attribute->column}` BINARY(16) $null;');";
        } else {
            $this->codegen->log('Unknown column type', $attribute->column, get_class($attribute));
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
    }
}
