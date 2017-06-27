<?php
namespace Rhino\Codegen\Template\Interfaces;

interface DatabaseReset {
    public function iterateDatabaseResetSql(): iterable;
}
