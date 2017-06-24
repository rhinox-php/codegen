<?php
namespace Rhino\Codegen\Database;

class MySql implements DatabaseInterface {
    public $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function databaseExists(string $databaseName): bool {
        $statement = $this->pdo->prepare('SHOW DATABASES LIKE ?');
        $statement->execute([
            $databaseName,
        ]);
        return $statement->rowCount() == 1;
    }

    public function tableExists(string $tableName): bool {
        $statement = $this->pdo->prepare('SHOW TABLES LIKE ?');
        $statement->execute([
            $tableName,
        ]);
        return $statement->rowCount() == 1;
    }

    public function getColumn(string $tableName, string $columnName): Column\MySql {
        return new Column\MySql($this, $tableName, $columnName);
    }
}
