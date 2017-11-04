<?php
namespace Rhino\Codegen\Template\Interfaces;

interface DatabaseMigrate
{
    public function iterateDatabaseMigrateSql(\PDO $pdo, string $date): iterable;
}
