<?= '<?php'; ?>

namespace <?= $this->getModelNamespace(); ?>;

abstract class AbstractModel implements \JsonSerializable {
    use \Rhino\Core\Model\MySqlModel;
    
    const DATE_FORMAT = DATE_ISO8601;
    const DATE_TIME_FORMAT = DATE_ISO8601;

    // Properties
    protected $id;
    protected $updated;
    protected $created;

    // Table name
    protected static $table;

    // Columns
    protected static $columns;

    // Datatable
    public static function getDataTable() {
        throw new \Exception('Expected child class to implement ' . __FUNCTION__);
    }
    
    // Json
    public abstract function jsonSerialize();

    // Save/insert/update/delete
    public function save() {
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
        $this->saveRelated();
    }

    protected abstract function insert();
    
    protected abstract function update();
    
    public abstract function delete();

    protected abstract function saveRelated();
    
    public static function findById($id) {
        throw new \Exception('Expected child class to implement ' . __FUNCTION__);
    }
    
    // ID accessors
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    // Created/updated date accessors
    public function getCreated() {
        return $this->created;
    }
    
    public function setCreated(\DateTimeImmutable $created) {
        $this->created = $created;
        return $this;
    }

    public function getUpdated() {
        return $this->updated;
    }
    
    public function setUpdated(\DateTimeImmutable $updated) {
        $this->updated = $updated;
        return $this;
    }
    
}
