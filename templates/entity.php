namespace <?= $codegen->getNamespace(); ?>;

class <?= $entity->getName(); ?> {

    protected $id;
    
    <?php foreach ($entity->getAttributes() as $attribute): ?>
    
    protected <?= $attribute->getName(); ?>;
    
    <?php endforeach; ?>
        
    protected $updated;
    protected $created;
    
    public function findById($id) {
        @todo
    }
    
    public function fetch<?= $entity->getName(); ?>() {
        @todo
    }
    
    public function fetch<?= $entity->getPluralName(); ?>() {
        while ($entity = $this->fetch<?= $entity->getName(); ?>() {
            yield $entity;
        }
    }
    
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

    public function getCreated() {
        return $this->created;
    }
    
    public function setCreated($created) {
        $this->created = $created;
        return $this;
    }

    public function getUpdated() {
        return $this->updated;
    }
    
    public function setUpdated($updated) {
        $this->updated = $updated;
        return $this;
    }
    
}
