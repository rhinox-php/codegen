<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

abstract class AbstractModel extends \Illuminate\Database\Eloquent\Model implements \JsonSerializable {

    // Iterate methods
    public static function iterateAll() {
        return static::all();
    }
    
    public static function getAll() {
        return iterator_to_array(static::iterateAll());
    }
    
    // Save 
    public function save(array $options = [])
    {
        \DB::transaction(function () {
            parent::save();
            $this->saveRelated();
        });
    }

    // ID accessors
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public static function findById($id) {
        return static::find($id);
    }
    
    // Created/updated date accessors
    public function getCreated() {
        return $this->created_at;
    }
    
    public function setCreated(\DateTimeImmutable $created) {
        $this->created_at = $created;
        return $this;
    }

    public function getUpdated() {
        return $this->updated_at;
    }
    
    public function setUpdated(\DateTimeImmutable $updated) {
        $this->updated_at = $updated;
        return $this;
    }
    
    // Camel case properties
    public function getAttribute($key)
    {
        return parent::getAttribute(snake_case($key));
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute(snake_case($key), $value);
    }
}
