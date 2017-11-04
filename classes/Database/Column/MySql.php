<?php
namespace Rhino\Codegen\Database\Column;

class MySql implements ColumnInterface
{
    const TYPE_INT = 'int';
    const TYPE_TINY_INT = 'tinyint';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_TEXT = 'text';
    const TYPE_VARCHAR = 'varchar';
    const TYPE_MEDIUM_TEXT = 'mediumtext';
    const TYPE_DATE = 'date';
    const TYPE_DATE_TIME = 'datetime';

    public $mysql;
    public $tableName;
    public $columnName;

    public function __construct(\Rhino\Codegen\Database\MySql $mysql, string $tableName, string $columnName)
    {
        $this->mysql = $mysql;
        $this->tableName = $tableName;
        $this->columnName = $columnName;
    }

    public function exists()
    {
        return !empty($this->getDescription());
    }

    public function getType()
    {
        if (preg_match('/^(?<type>[a-z]+)[^a-z]*/', $this->getDescription()['Type'], $matches)) {
            return $matches['type'];
        }
        return null;
    }

    public function isType(string $type): bool
    {
        return $this->getType() == $type;
    }

    public function getSize()
    {
        if (preg_match('/^[a-z]+\((?<size>[0-9]+)\)/', $this->getDescription()['Type'], $matches)) {
            return (int) $matches['size'];
        }
        return null;
    }

    public function getDecimalSize()
    {
        if (preg_match('/^[a-z]+\((?<size>[0-9]+),(?<dp>[0-9]+)\)/', $this->getDescription()['Type'], $matches)) {
            return [
                'size' => (int) $matches['size'],
                'dp' => (int) $matches['dp'],
            ];
        }
        return null;
    }

    public function isSize(int $size, int $dp = null): bool
    {
        return $this->getSize() == $size;
    }

    public function isDecimalSize(int $size, int $dp): bool
    {
        ['size' => $actualSize, 'dp' => $actualDp] = $this->getDecimalSize();
        return $actualSize == $size && $actualDp == $dp;
    }

    public function isSigned(): bool
    {
        return !preg_match('/^[a-z]+\([0-9]+\) unsigned/', $this->getDescription()['Type']);
    }

    public function isNullable()
    {
    }

    public function isIndexed()
    {
    }

    protected function getDescription(): array
    {
        try {
            $statement = $this->mysql->pdo->prepare("SHOW COLUMNS FROM `$this->tableName` LIKE ?");
            $statement->execute([
                $this->columnName,
            ]);
            if ($statement->rowCount() != 1) {
                return [];
            }
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $exception) {
            return [];
        }
    }
}
