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
        $migrationNumber = 1;
        foreach ($this->codegen->getEntities() as $entity) {
            $path = $this->getFilePath('generic/sql/migrate', 'src/sql/up/' . $date . '.sql', [
                'date' => $date,
                'entity' => $entity,
            ]);
            if (!$this->codegen->db->tableExists($entity->getPluralTableName())) {
                $this->codegen->log('Creating table', $entity->getPluralTableName());
                yield $path => "
                    Schema::create('{$entity->getPluralTableName()}', function (Blueprint \$table) {
                        \$table->increments('id');
                        \$table->dateTime('created_at')->nullable()->default(null);
                        \$table->dateTime('updated_at')->nullable()->default(null);
                    });
                ";
            }
            $columnMigrations = [];
            foreach ($this->migrateColumns($entity, $path) as $columnMigration) {
                $columnMigrations[] = $columnMigration;
            }
            if (!empty($columnMigrations)) {
                $columnMigrations = implode("\n" . str_repeat(' ', 32), $columnMigrations);
                yield $path => "
                    <?php
                    use Illuminate\Database\Schema\Blueprint;

                    class Migration_{$date}_{$migrationNumber} extends \Illuminate\Database\Migrations\Migration
                    {
                        public function up()
                        {
                            Schema::table('{$entity->getPluralTableName()}', function (Blueprint \$table) {
                                $columnMigrations
                            });
                        }

                        public function down()
                        {
                            Schema::table('{$entity->getPluralTableName()}', function (Blueprint \$table) {
                            });
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
                if ($attribute instanceof Attribute\IntAttribute) {
                    yield $path => "\$table->integer('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
                } elseif ($attribute instanceof Attribute\DecimalAttribute) {
                    yield $path => "\$table->decimal('{$attribute->getColumnName()}', 10, 2)->nullable()->after('{$previous}');";
                } elseif ($attribute instanceof Attribute\BoolAttribute) {
                    yield $path => "\$table->bool('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
                } elseif ($attribute instanceof Attribute\TextAttribute) {
                    yield $path => "\$table->mediumText('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
                } elseif ($attribute instanceof Attribute\StringAttribute) {
                    yield $path => "\$table->string('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
                } elseif ($attribute instanceof Attribute\DateAttribute) {
                    yield $path => "\$table->date('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
                } elseif ($attribute instanceof Attribute\DateTimeAttribute) {
                    yield $path => "\$table->dateTime('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
                }
            } else {
                yield from $this->migrateColumn($entity, $attribute, $column, $previous, $path);
            }
            $previous = $attribute->getColumnName();
        }
    }

    private function migrateColumn(Entity $entity, Attribute $attribute, MySqlColumn $column, string $previous, string $path): iterable
    {
        if ($attribute instanceof Attribute\IntAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_INT) || !$column->isSize(11) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to INT(11) SIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield $path => "\$table->integer('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
            }
        } elseif ($attribute instanceof Attribute\DecimalAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_DECIMAL) || !$column->isDecimalSize(10, 2) || !$column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to DECIMAL(10, 2) from', $column->getType(), $column->getDecimalSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield $path => "\$table->decimal('{$attribute->getColumnName()}', 10, 2)->nullable()->after('{$previous}');";
            }
        } elseif ($attribute instanceof Attribute\BoolAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_TINY_INT) || !$column->isSize(1) || $column->isSigned()) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to TINYINT(1) UNSIGNED from', $column->getType(), $column->getSize(), $column->isSigned() ? 'SIGNED' : 'UNSIGNED', 'in', $entity->getPluralTableName());
                yield $path => "\$table->bool('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
            }
        } elseif ($attribute instanceof Attribute\TextAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_MEDIUM_TEXT)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to MEDIUMTEXT from', $column->getType(), 'in', $entity->getPluralTableName());
                yield $path => "\$table->mediumText('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
            }
        } elseif ($attribute instanceof Attribute\StringAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_VARCHAR) || !$column->isSize(255)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to VARCHAR(255) from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield $path => "\$table->string('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
            }
        } elseif ($attribute instanceof Attribute\DateAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_DATE)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to DATE from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield $path => "\$table->date('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
            }
        } elseif ($attribute instanceof Attribute\DateTimeAttribute) {
            if (!$column->isType(MySqlColumn::TYPE_DATE_TIME)) {
                $this->codegen->log('Changing column', $attribute->getColumnName(), 'to DATETIME from', $column->getType(), $column->getSize(), 'in', $entity->getPluralTableName());
                yield $path => "\$table->dateTime('{$attribute->getColumnName()}')->nullable()->after('{$previous}');";
            }
        }

        // @todo check indexes
        // @todo check nullable
        // @todo check collation
    }
}
