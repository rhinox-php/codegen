<?= '<?php'; ?>

namespace <?= $codegen->getNamespace(); ?>;

class Application {

    protected static $current;

    protected $url;
    protected $domain;
    protected $secure;
    protected $pdo;
    protected $pdoCallback;

    public function __construct($url, $pdoCallback) {
        $parsedUrl = parse_url($url);
        $this->url = $url;
        $this->domain = $parsedUrl['host'];
        $this->secure = $parsedUrl['scheme'] === 'https';
        $this->pdoCallback = $pdoCallback;

        static::$current = $this;
    }

    public function getPdo() {
        if (!$this->pdo) {
            $this->pdo = call_user_func($this->pdoCallback);
        }
        return $this->pdo;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function isSecure() {
        return $this->secure;
    }

    public static function current() {
        return static::$current;
    }

}
