<?php
namespace Rhino\Codegen\Database\Table;
use Rhino\Codegen\Database\Column;

class MySql implements TableInterface
{
    public $mysql;
    public $tableName;

    public function __construct(\Rhino\Codegen\Database\MySql $mysql, string $tableName)
    {
        $this->mysql = $mysql;
        $this->tableName = $tableName;
    }

    public function getName() {
        return $this->tableName;
    }

    public function iterateColumns(): \Generator
    {
        $statement = $this->mysql->pdo->prepare("SHOW COLUMNS FROM `$this->tableName`");
        $statement->execute();
        foreach ($statement->fetchAll(\PDO::FETCH_COLUMN) as $columnName) {
            yield new Column\MySql($this->mysql, $this->tableName, $columnName);
        }
    }
}
