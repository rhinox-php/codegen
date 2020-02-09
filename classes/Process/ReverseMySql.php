<?php

namespace Rhino\Codegen\Process;

use Rhino\Codegen\Codegen;
use Rhino\Codegen\Attribute;
use Rhino\Codegen\Relationship;

class ReverseMySql
{
    protected $codegen;
    private $entities = [];

    public function __construct(Codegen $codegen)
    {
        $this->codegen = $codegen;
    }

    public function getXml()
    {
        $inflector = \ICanBoogie\Inflector::get(\ICanBoogie\Inflector::DEFAULT_LOCALE);
        $pdo = $this->codegen->getPdo();
        // $this->codegen->setEntities([]);
        $this->processEntities();
        $this->processAttributes();
        (new XmlSerializer($this->entities))->serialize();
        // foreach((new Description($this->codegen))->describe() as $line) {
        //     echo $line . PHP_EOL;
        // }
    }

    public function processEntities()
    {
        foreach ($this->codegen->db->iterateTables() as $table) {
            $name = $table->getName();
            $humanName = $this->humanize($name);
            // var_dump($this->humanize($name));
            // var_dump($table);

            $entity = new Reverse\Node('entity');
            $this->entities[$name] = $entity;
            // $this->codegen->addEntity($entity);
        }
    }

    public function processAttributes()
    {
        foreach ($this->codegen->db->iterateTables() as $table) {
            $name = $table->getName();
            $humanName = $this->humanize($name);

            $entity = $this->entities[$name];

            foreach ($table->iterateColumns() as $column) {
                switch ($column->getName()) {
                    case 'id':
                    case 'created':
                    case 'updated': {
                            continue 2;
                        }
                }
                $humanName = $this->humanize($column->getName());
                switch ($column->getType()) {
                    case 'varchar':
                        $attribute = new Reverse\Attribute($column->getName(), 'string');
                        $entity->setAttribute($column->getName(), $attribute);
                        break;

                    case 'int':
                        $attribute = new Reverse\Attribute($column->getName(), 'int');
                        $entity->setAttribute($column->getName(), $attribute);
                        break;

                    case 'datetime':
                        $attribute = new Reverse\Attribute($column->getName(), 'date-time');
                        $entity->setAttribute($column->getName(), $attribute);
                        break;

                    case 'decimal':
                        $attribute = new Reverse\Attribute($column->getName(), 'decimal');
                        $entity->setAttribute($column->getName(), $attribute);
                        break;
                }
            }
        }
    }

    public function humanize(string $string): string
    {
        $string = preg_replace('/[^a-z0-9]+/i', ' ', $string);
        $string = ucwords($string);
        return $string;
    }
}
