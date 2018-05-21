<?php
namespace Rhino\Codegen\Process;
use Rhino\Codegen\Codegen;
use Rhino\Codegen\Attribute;
use Rhino\Codegen\Relationship;

class ReverseMySql
{
    protected $codegen;

    public function __construct(Codegen $codegen) {
        $this->codegen = $codegen;
    }

    public function getXml() {
        $inflector = \ICanBoogie\Inflector::get(\ICanBoogie\Inflector::DEFAULT_LOCALE);
        $pdo = $this->codegen->getPdo();
        $this->codegen->setEntities([]);
        $this->processEntities();
        $this->processAttributes();
        (new XmlSerializer($this->codegen))->serialize();
        // foreach((new Description($this->codegen))->describe() as $line) {
        //     echo $line . PHP_EOL;
        // }
    }

    public function processEntities() {
        foreach ($this->codegen->db->iterateTables() as $table) {
            $name = $table->getName();
            $humanName = $this->humanize($name);
            // var_dump($this->humanize($name));
            // var_dump($table);

            $entity = new \Rhino\Codegen\Entity();
            $entity->setType('entity');
            $entity->setName($humanName);

            $this->codegen->addEntity($entity);
        }
    }

    public function processAttributes() {
        foreach ($this->codegen->db->iterateTables() as $table) {
            $name = $table->getName();
            $humanName = $this->humanize($name);

            $entity = $this->codegen->findEntity($humanName);

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
                    case 'varchar': {
                        $attribute = new Attribute\StringAttribute();
                        $attribute->setName($humanName);
                        $attribute->setColumnName($column->getName());
                        $entity->addAttribute($attribute);
                        break;
                    }
                    case 'int': {
                        $attribute = new Attribute\IntAttribute();
                        $attribute->setName($humanName);
                        $attribute->setColumnName($column->getName());
                        $entity->addAttribute($attribute);
                        if (preg_match('/^([a-z0-9_]+)_id$/i', $column->getName(), $matches)) {
                            $toName = $this->humanize($matches[1]);
                            try {
                                $to = $this->codegen->findEntity($toName);
                                $attribute->setHasAccessors(false);
                                $attribute->setIsForeignKey(true);
                                $attribute->setIsIndexed(true);

                                $relationship = new Relationship\BelongsTo();
                                $relationship->setFrom($entity);
                                $relationship->setTo($to);
                                $relationship->setName($toName);
                                $entity->addRelationship($relationship);
                                $to->addRelationship($relationship);

                                $relationship = new Relationship\HasMany();
                                $relationship->setFrom($to);
                                $relationship->setTo($entity);
                                $relationship->setName($entity->getName());
                                $to->addRelationship($relationship);
                                $entity->addRelationship($relationship);
                            } catch (\Exception $e) {
                                $this->codegen->log($e->getMessage());
                            }
                            break;
                        }
                        break;
                    }
                    case 'datetime': {
                        $attribute = new Attribute\DateTimeAttribute();
                        $attribute->setName($humanName);
                        $attribute->setColumnName($column->getName());
                        $entity->addAttribute($attribute);
                        break;
                    }
                    case 'decimal': {
                        $attribute = new Attribute\DecimalAttribute();
                        $attribute->setName($humanName);
                        $attribute->setColumnName($column->getName());
                        $entity->addAttribute($attribute);
                        break;
                    }
                }
            }
        }
    }

    public function humanize(string $string): string {
        $string = preg_replace('/[^a-z0-9]+/i', ' ', $string);
        $string = ucwords($string);
        return $string;
    }
}
