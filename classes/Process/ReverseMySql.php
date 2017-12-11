<?php
namespace Rhino\Codegen\Process;
use Rhino\Codegen\Codegen;

class ReverseMySql
{
    protected $codegen;

    public function __construct(Codegen $codegen) {
        $this->codegen = $codegen;
    }

    public function getXml() {
        $inflector = \ICanBoogie\Inflector::get(\ICanBoogie\Inflector::DEFAULT_LOCALE);
        $pdo = $this->codegen->getPdo();
        foreach ($this->codegen->db->iterateTables() as $table) {
            $name = $table->getName();
            $humanName = $inflector->humanize($name);
            // var_dump($inflector->humanize($name));
            // var_dump($table);

            $entity = new \Rhino\Codegen\Entity();
            $entity->setType('entity');
            $entity->setName($humanName);
            $this->codegen->addEntity($entity);

            foreach ($table->iterateColumns() as $column) {
                var_dump($column);
            }
        }
        foreach((new Description($this->codegen))->describe() as $line) {
            echo $line . PHP_EOL;
        }
    }
}
