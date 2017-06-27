<?php
namespace Rhino\Codegen\Database;

interface DatabaseInterface {
    public function databaseExists(string $databaseName): bool;
    public function tableExists(string $tableName): bool;
}
