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
        foreach ($this->codegen->getEntities() as $entity) {
            $path = $this->getFilePath('generic/sql/migrate', 'database/migrations/' . $date . '_' . $entity->getTableName() . '_' . $migrationNumber . '.php', [
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

                    class {$entity->getClassName()}{$migrationNumber} extends \Illuminate\Database\Migrations\Migration
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
        foreach ($entity->getAttributes() as $attribute) {
            $column = $this->codegen->db->getColumn($entity->getPluralTableName(), $attribute->getColumnName());
            if (!$column->exists()) {
                $this->codegen->log('Creating column', $attribute->getColumnName(), 'in', $entity->getPluralTableName());
                if ($attribute->isType(['int'])) {
                    yield [
                        "\$table->integer('{$attribute->getColumnName()}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                    ];
                } elseif ($attribute->isType(['Decimal'])) {
                    yield [
                        "\$table->decimal('{$attribute->getColumnName()}', 10, 2)->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                    ];
                } elseif ($attribute->isType(['Bool'])) {
                    yield [
                        "\$table->bool('{$attribute->getColumnName()}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                    ];
                } elseif ($attribute->isType(['Text', 'Json'])) {
                    yield [
                        "\$table->mediumText('{$attribute->getColumnName()}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                    ];
                } elseif ($attribute->isType(['String'])) {
                    yield [
                        "\$table->string('{$attribute->getColumnName()}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                    ];
                } elseif ($attribute->isType(['Date'])) {
                    yield [
                        "\$table->date('{$attribute->getColumnName()}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                    ];
                } elseif ($attribute->isType(['DateTime'])) {
                    yield [
                        "\$table->dateTime('{$attribute->getColumnName()}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                    ];
                } elseif ($attribute->isType(['Password'])) {
                    yield [
                        "\$table->string('{$attribute->getColumnName()}')->nullable()->after('{$previous}');",
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                    ];
                } elseif ($attribute->isType(['Uuid'])) {
                    $null = $attribute->isNullable() ? 'NULL' : 'NOT NULL';
                    yield [
                        null,
                        "\$table->dropColumn('{$attribute->getColumnName()}');",
                        "\DB::statement('ALTER TABLE `{$entity->getPluralTableName()}` ADD `{$attribute->getColumnName()}` BINARY(16) $null AFTER `{$previous}`;');",
                    ];
                } else {
                    $this->codegen->log('Unknown column type', $attribute->getColumnName(), get_class($attribute));
                }
            } else {
                yield from $this->migrateColumn($entity, $attribute, $column, $previous, $path);
            }
            $previous = $attribute->getColumnName();
        }
    }

    private function migrateColumn(Entity $entity, Attribute $attribute, MySqlColumn $column, string $previous, string $path): iterable
    {
        if ($attribute->isType(['Int'])) {
            if (!$column->isType(MySqlColumn::TYPE_INT) || !$column->isSize(11) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to INT(11) SIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield [
                    "\$table->integer('{$attribute->getColumnName()}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->isType(['Decimal'])) {
            if (!$column->isType(MySqlColumn::TYPE_DECIMAL) || !$column->isDecimalSize(10, 2) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to DECIMAL(10, 2) from', $column->getType(), $column->getDecimalSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield [
                    "\$table->decimal('{$attribute->getColumnName()}', 10, 2)->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->isType(['Bool'])) {
            if (!$column->isType(MySqlColumn::TYPE_TINY_INT) || !$column->isSize(1) || $column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to TINYINT(1) UNSIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield [
                    "\$table->bool('{$attribute->getColumnName()}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->isType(['Text', 'Json'])) {
            if (!$column->isType(MySqlColumn::TYPE_MEDIUM_TEXT)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to MEDIUMTEXT from', $column->getType(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->mediumText('{$attribute->getColumnName()}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->isType(['String'])) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(255)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to VARCHAR(255) from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->string('{$attribute->getColumnName()}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->isType(['Date'])) {
            if (!$column->isType(MySqlColumn::TYPE_DATE)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to DATE from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->date('{$attribute->getColumnName()}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->isType(['DateTime'])) {
            if (!$column->isType(MySqlColumn::TYPE_DATE_TIME)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to DATETIME from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->dateTime('{$attribute->getColumnName()}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->isType(['Password'])) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(255)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to VARCHAR(255) from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield [
                    "\$table->string('{$attribute->getColumnName()}')->nullable()->change();",
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                ];
            }
        } elseif ($attribute->isType(['Uuid'])) {
            if (!$column->isType(MySqlColumn::TYPE_BINARY) || !$column->isSize(16) || $column->isNullable() != $attribute->isNullable()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to BINARY(16) from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                $null = $attribute->isNullable() ? 'NULL' : 'NOT NULL';
                yield [
                    null,
                    $this->reverseMigrateColumn($entity, $attribute, $column),
                    "\DB::statement('ALTER TABLE `{$entity->getPluralTableName()}` CHANGE `{$attribute->getColumnName()}` `{$attribute->getColumnName()}` BINARY(16) $null;');",
                ];
            }
        } else {
            $this->codegen->log('Unknown column type', $attribute->getColumnName(), get_class($attribute));
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
    }

    private function reverseMigrateColumn(Entity $entity, Attribute $attribute, MySqlColumn $column): ?string
    {
        if ($column->isType(MySqlColumn::TYPE_INT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to INT(11) SIGNED in', $entity->getPluralTableName());
            return "\$table->integer('{$attribute->getColumnName()}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DECIMAL)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to DECIMAL(10, 2) in', $entity->getPluralTableName());
            return "\$table->decimal('{$attribute->getColumnName()}', 10, 2)->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_TINY_INT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to TINYINT(1) UNSIGNED in', $entity->getPluralTableName());
            return "\$table->bool('{$attribute->getColumnName()}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_MEDIUM_TEXT)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to MEDIUMTEXT in', $entity->getPluralTableName());
            return "\$table->mediumText('{$attribute->getColumnName()}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_VARCHAR)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to VARCHAR(255) in', $entity->getPluralTableName());
            return "\$table->string('{$attribute->getColumnName()}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DATE)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to DATE in', $entity->getPluralTableName());
            return "\$table->date('{$attribute->getColumnName()}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_DATE_TIME)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to DATETIME in', $entity->getPluralTableName());
            return "\$table->dateTime('{$attribute->getColumnName()}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_VARCHAR)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to VARCHAR(255) in', $entity->getPluralTableName());
            return "\$table->string('{$attribute->getColumnName()}')->nullable()->change();";
        } elseif ($column->isType(MySqlColumn::TYPE_BINARY)) {
            $this->codegen->log('[DOWN] Reverting column', $attribute->getColumnName(), 'to BINARY(16) in', $entity->getPluralTableName());
            $null = $attribute->isNullable() ? 'NULL' : 'NOT NULL';
            return "\DB::statement('ALTER TABLE `{$entity->getPluralTableName()}` CHANGE `{$attribute->getColumnName()}` `{$attribute->getColumnName()}` BINARY(16) $null;');";
        } else {
            $this->codegen->log('Unknown column type', $attribute->getColumnName(), get_class($attribute));
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
    }
}
