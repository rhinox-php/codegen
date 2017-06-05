<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class PdoModel {
    protected static $pdoCallback;
    protected static $pdo;

    public static function getPdo(): \PDO {
        if (!static::$pdo) {
            assert(is_callable(static::$pdoCallback), new \Exception('Expected PDO callback to be set and callable.'));
            static::$pdo = call_user_func(static::$pdoCallback);
        }
        return static::$pdo;
    }

    public static function setPdoCallback(callable $pdoCallback) {
        static::$pdoCallback = $pdoCallback;
    }
}
