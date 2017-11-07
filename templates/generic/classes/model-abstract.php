<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

abstract class AbstractModel implements \JsonSerializable, \Rhino\JsonApiList\ModelInterface {

    const DATE_FORMAT = 'Y-m-d';
    const DATE_TIME_FORMAT = DATE_ISO8601;

    const MYSQL_DATE_FORMAT = 'Y-m-d';
    const MYSQL_DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    // Properties
    protected $id;
    protected $updated;
    protected $created;

    // Table name
    protected static $table;

    // Columns
    protected static $columns;

    protected static $transactionCount = 0;

    // Datatable
    public static function getDataTable() {
        throw new \Exception('Expected child class to implement ' . __FUNCTION__);
    }

    public static function getTableName() {
        return static::$table;
    }

    // Json
    public abstract function jsonSerialize();

    // Save/insert/update/delete
    public function save(callable $transactionCallback = null) {
        $this->transaction(function() use($transactionCallback) {
            if ($this->getId()) {
                $this->update();
            } else {
                $this->insert();
            }
            $this->saveRelated();
            if ($transactionCallback) {
                $transactionCallback();
            }
        });
    }

    protected abstract function insert();

    protected abstract function update();

    public abstract function delete();

    protected abstract function saveRelated();

    public static function getPdo() {
        return PdoModel::getPdo();
    }

    protected static function fetchObject(\PDOStatement $result) {
        return static::hydrateFromPdoStatement($result);
    }

    protected static function query($sql, array $data = []) : \PDOStatement {
        $statement = static::getPdo()->prepare($sql);
        $statement->execute($data);
        return $statement;
    }

    protected static function transaction(callable $transactionCallback) {
        $pdo = static::getPdo();
        try {
            if (static::$transactionCount === 0) {
                $pdo->beginTransaction();
            }
            static::$transactionCount++;
            $transactionCallback();
            static::$transactionCount--;
            if (static::$transactionCount === 0) {
                $pdo->commit();
            }
        } catch (\Exception $exception) {
            static::$transactionCount = 0;
            $pdo->rollBack();
            throw new \Exception('Exception throw while in transaction, see previous exception.', null, $exception);
        }
    }

    protected static function lastInsertId() {
        return static::getPdo()->lastInsertId();
    }

    public static function formatMySqlDate(\DateTimeInterface $date) {
        return (clone $date)->setTimezone(new \DateTimeZone('UTC'))->format(static::MYSQL_DATE_FORMAT);
    }

    public static function formatMySqlDateTime(\DateTimeInterface $date) {
        return (clone $date)->setTimezone(new \DateTimeZone('UTC'))->format(static::MYSQL_DATE_TIME_FORMAT);
    }

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
