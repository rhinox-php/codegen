<?= '<?php'; ?>

namespace <?= $codegen->getNamespace(); ?>\Model;

class <?= $entity->getName(); ?> {

    protected $id;
    
    <?php foreach ($entity->getAttributes() as $attribute): ?>
    protected $<?= $attribute->getName(); ?>;
    <?php endforeach; ?>
        
    protected $updated;
    protected $created;
    
    protected static $table = '<?= $entity->getTableName(); ?>';
    
    protected static $columns = '
        <?= $entity->getTableName(); ?>.id,
        <?php foreach ($entity->getAttributes() as $attribute): ?>
            <?= $entity->getTableName(); ?>.<?= $attribute->getColumnName(); ?> AS <?= $attribute->getName(); ?>,
        <?php endforeach; ?>

        <?= $entity->getTableName(); ?>.created,
        <?= $entity->getTableName(); ?>.updated
    ';

    <?php foreach ($entity->getRelationships() as $relationship): ?>

        <?php if ($entity == $relationship->getFrom()): ?>

            protected $<?= $relationship->getTo()->getPropertyName(); ?>;

        <?php endif; ?>

    <?php endforeach; ?>

    public static function getDataTable() {
        $table = new \Rhino\DataTable\MySqlDataTable(static::getPdo(), 'contacts');
        $table->insertColumn('actions', function($column, $row) {
        // @todo fix delete button, make post, confirm
        return '
            <a href="/contact/edit/' . $row['id'] . '" class="btn btn-xs btn-default">Edit</a>
            <a href="/contact/delete/' . $row['id'] . '" class="btn btn-xs btn-link text-danger">Delete</a>
        ';
        })->setLabel('Actions');
        $table->addColumn('id')->setLabel('ID');
        $table->addColumn('created')->setLabel('Created');
        $table->addColumn('updated')->setLabel('Updated');
        return $table;
    }

    public function findById($id) {
        return $this->fetch<?= $entity->getName(); ?>($this->query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . '
            WHERE id = :id;
        ', [
            ':id' => $this->getId(),
        ]));
    }
    
    protected function fetch<?= $entity->getName(); ?>($result) {
        $entity = $result->fetchObject(static::class);
        $entity->setCreated(new \DateTimeImmutable($this->created));
        $entity->setUpdated(new \DateTimeImmutable($this->updated));
        return $entity;
    }

    protected function fetch<?= $entity->getPluralName(); ?>() {
        while ($entity = $this->fetch<?= $entity->getName(); ?>()) {
            yield $entity;
        }
    }

    <?php foreach ($entity->getRelationships() as $relationship): ?>

    <?php if ($entity == $relationship->getFrom()): ?>

    public function fetch<?= $relationship->getTo()->getPluralName(); ?>() {
        if (!$this-><?= $relationship->getTo()->getPropertyName(); ?>) {
            $this->$this-><?= $relationship->getTo()->getPropertyName(); ?> = <?= $relationship->getTo()->getName(); ?>::findBy<?= $entity->getName(); ?>Id($this->getId());
        }
        return $this-><?= $relationship->getTo()->getPropertyName(); ?>;
    }

    <?php endif; ?>

    <?php if ($entity == $relationship->getTo()): ?>

    public static function findBy<?= $relationship->getFrom()->getName(); ?>Id($id) {
        return static::fetchAddresses(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . '
            JOIN <?= $relationship->getFrom()->getTableName(); ?>_<?= $relationship->getTo()->getTableName(); ?> ON
                <?= $relationship->getFrom()->getTableName(); ?>_<?= $relationship->getTo()->getTableName(); ?>.<?= $relationship->getFrom()->getTableName(); ?>_id = :id
                AND <?= $relationship->getFrom()->getTableName(); ?>_<?= $relationship->getTo()->getTableName(); ?>.<?= $relationship->getTo()->getTableName(); ?>_id = ' . static::$table . '.id
        ', [
            ':id' => $id,
        ]));
    }

    <?php endif; ?>

    <?php endforeach; ?>
    
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    <?php foreach ($entity->getAttributes() as $attribute): ?>
    
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getName(); ?>;
    }
        
    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getName(); ?> = $value;
        return $this;
    }
    
    <?php endforeach; ?>

    public function getCreated(): \DateTimeImmutable {
        return $this->created;
    }
    
    public function setCreated(\DateTimeImmutable $created) {
        $this->created = $created;
        return $this;
    }

    public function getUpdated(): \DateTimeImmutable {
        return $this->updated;
    }
    
    public function setUpdated(\DateTimeImmutable $updated) {
        $this->updated = $updated;
        return $this;
    }
    
}
