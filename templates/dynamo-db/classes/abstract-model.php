<?= '<?php'; ?>

namespace <?= $this->getNamespace(); ?>;

abstract class AbstractModel implements \JsonSerializable {

    protected $id;
    protected $createdAt;
    protected $updatedAt;

    public static function getAwsSdk() {
        return new \Aws\Sdk([
            'endpoint' => 'http://localhost:8000',
            'region' => 'us-west-1',
            'version' => 'latest',
            'credentials' => [
                'key' => 'key',
                'secret' => 'secret',
            ],
        ]);
    }

    public static function getDynamoDbClient() {
        return static::getAwsSdk()->createDynamoDb();
    }

    // Iterate methods
    public static function iterateAll() {
        $response = static::getDynamoDbClient()->scan([
            'TableName' => static::getTableName(),
        ]);
        
        foreach ($response['Items'] as $item) {
            yield static::fetchInstance($item);
        }
    }
    
    public static function getAll() {
        return iterator_to_array(static::iterateAll());
    }

    // ID accessors
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function generateId() {
        $id = (new \DateTime())->format('ymdhis');
        $id .= preg_replace('/^0\./', '', explode(' ', microtime())[0]);
        $id = substr($id, 0, 19);
        //9223372036854775807
        return $id;
    }
    
    // Created/updated date accessors
    public function getCreated() {
        return $this->createdAt;
    }
    
    public function setCreated(\DateTimeImmutable $created) {
        $this->createdAt = $created;
        return $this;
    }

    public function getUpdated() {
        return $this->updatedAt;
    }
    
    public function setUpdated(\DateTimeImmutable $updated) {
        $this->updatedAt = $updated;
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
