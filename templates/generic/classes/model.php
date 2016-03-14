<?= '<?php'; ?>

namespace <?= $this->getModelNamespace(); ?>;

class <?= $entity->getClassName(); ?> implements \JsonSerializable {
    use \Rhino\Core\Model\MySqlModel;

    // Properties
    protected $id;
<?php foreach ($entity->getAttributes() as $attribute): ?>
    protected $<?= $attribute->getPropertyName(); ?>;
<?php endforeach; ?>
    protected $updated;
    protected $created;

    //Related entities
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\OneToMany): ?>
    protected $<?= $relationship->getTo()->getPluralPropertyName(); ?> = null;
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
    protected $<?= $relationship->getTo()->getPluralPropertyName(); ?> = null;
<?php else: ?>
    protected $<?= $relationship->getTo()->getPropertyName(); ?> = null;
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>

    // Table name
    protected static $table = '<?= $entity->getTableName(); ?>';

    // Columns
    protected static $columns = '
        <?= $entity->getTableName(); ?>.id,
<?php foreach ($entity->getAttributes() as $attribute): ?>
        <?= $entity->getTableName(); ?>.<?= $attribute->getColumnName(); ?> AS <?= $attribute->getPropertyName(); ?>,
<?php endforeach; ?>
        <?= $entity->getTableName(); ?>.created,
        <?= $entity->getTableName(); ?>.updated
    ';

    // Datatable
    public static function getDataTable() {
        $table = new \Rhino\DataTable\MySqlDataTable(static::getPdo(), '<?= $entity->getTableName(); ?>');
        $table->insertColumn('actions', function($column, $row) {
            // @todo fix delete button, make post, confirm
            return '
                <a href="/<?= $entity->getRouteName(); ?>/edit/' . $row['id'] . '" class="btn btn-xs btn-default">Edit</a>
                <a href="/<?= $entity->getRouteName(); ?>/delete/' . $row['id'] . '" class="btn btn-xs btn-link text-danger">Delete</a>
            ';
        })->setLabel('Actions');
        $table->addColumn('id')->setLabel('ID');
<?php foreach ($entity->getAttributes() as $attribute): ?>
        $table->addColumn('<?= $attribute->getColumnName(); ?>')->setLabel('<?= $attribute->getLabel(); ?>');
<?php endforeach; ?>
        $table->addColumn('created')->setLabel('Created');
        $table->addColumn('updated')->setLabel('Updated');
        return $table;
    }
    
    // Json
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->get<?= $attribute->getMethodName(); ?>()->format('Y-m-d') : null,
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->is<?= $attribute->getMethodName(); ?>(),
<?php else: ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php endif; ?>
<?php endforeach; ?>
        ];
    }

    // Save/insert/update/delete
    public function save() {
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
        $this->saveRelated();
    }

    protected function insert() {
        if (!$this->getCreated()) {
            $this->setCreated(new \DateTimeImmutable());
        }
        $this->query('
            INSERT INTO <?= $entity->getTableName(); ?> (
<?php foreach ($entity->getAttributes() as $attribute): ?>
                <?= $attribute->getColumnName(); ?>,
<?php endforeach; ?>
                created
            ) VALUES (
<?php foreach ($entity->getAttributes() as $attribute): ?>
                :<?= $attribute->getColumnName(); ?>,
<?php endforeach; ?>
                :created
            );
        ', [
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->get<?= $attribute->getMethodName(); ?>()->format('Y-m-d') : null,
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->is<?= $attribute->getMethodName(); ?>() ? 1 : 0,
<?php else: ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php endif; ?>
<?php endforeach; ?>
            ':created' => $this->formatDateTime($this->getCreated()),
        ]);
        
        $this->setId($this->lastInsertId());
    }
    
    protected static function formatDateTime(\DateTimeImmutable $date) {
        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    protected function update() {
        $this->query('
            UPDATE <?= $entity->getTableName(); ?>

            SET
<?php foreach ($entity->getAttributes() as $attribute): ?>
                <?= $attribute->getColumnName(); ?> = :<?= $attribute->getColumnName(); ?>,
<?php endforeach; ?>
                updated = UTC_TIMESTAMP()
            WHERE id = :id
            LIMIT 1;
        ', [
            ':id' => $this->getId(),
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->get<?= $attribute->getMethodName(); ?>()->format('Y-m-d') : null,
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->is<?= $attribute->getMethodName(); ?>() ? 1 : 0,
<?php else: ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php endif; ?>
<?php endforeach; ?>
        ]);
        
        $this->setUpdated(new \DateTimeImmutable());
    }
    
    public function delete() {
        $this->query('
            DELETE FROM <?= $entity->getTableName(); ?>

            WHERE id = :id;
        ', [
            ':id' => $this->getId(),
        ]);
    }

    protected function saveRelated() {
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\OneToMany): ?>
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> !== null) {
            if (empty($this-><?= $relationship->getTo()->getPluralPropertyName(); ?>)) {
                $this->query('
                    DELETE FROM <?= $relationship->getTo()->getTableName(); ?>

                    WHERE <?= $relationship->getFrom()->getTableName(); ?>_id = ?;
                ', [$this->getId()]);
            } else {
                $deleteBindings = [];
                foreach ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> as $relatedEntity) {
                    $relatedEntity->set<?= $relationship->getFrom()->getClassName(); ?>Id($this->getId());
                    $relatedEntity->save();
                    $deleteBindings[] = $relatedEntity->getId();
                }
                $in = implode(', ', array_fill(0, count($deleteBindings), '?'));
                $deleteBindings[] = $this->getId();
                $this->query("
                    DELETE FROM <?= $relationship->getTo()->getTableName(); ?>

                    WHERE 
                        id NOT IN ($in)
                        AND <?= $relationship->getFrom()->getTableName(); ?>_id = ?;
                ", $deleteBindings);
            }
        }
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
    }

    // @todo delete

    // Find methods
    public static function findById($id) {
        return static::fetch<?= $entity->getClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . '
            WHERE id = :id;
        ', [
            ':id' => $id,
        ]));
    }
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute ||
        $attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>

    // Find by attribute <?= $attribute->getName(); ?>

    public static function findBy<?= $attribute->getMethodName(); ?>($value) {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . '
            WHERE <?= $attribute->getColumnName(); ?> = :value;
        ', [
            ':value' => $value,
        ]));
    }

    public static function findFirstBy<?= $attribute->getMethodName(); ?>($value) {
        $result = null;
        foreach (static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . '
            WHERE <?= $attribute->getColumnName(); ?> = :value;
        ', [
            ':value' => $value,
        ])) as $entity) {
            assert(!$result, 'Expected there to only be one <?= $entity->getClassName(); ?>');
            $result = $entity;
        };
        return $result;
    }

    public static function countBy<?= $attribute->getMethodName(); ?>($value) {
        return (int) static::query('
            SELECT COUNT(id)
            FROM ' . static::$table . '
            WHERE <?= $attribute->getColumnName(); ?> = :value;
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }
<?php endif; ?>
<?php endforeach; ?>

    // Iterate methods
    public static function iterateAll() {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . ';
        '));
    }

    // Fetch methods
    protected static function fetch<?= $entity->getClassName(); ?>($result) {
        $entity = $result->fetchObject(static::class);
        if (!$entity) {
            return null;
        }

        // Parse date attributes
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
        $entity->set<?= $attribute->getMethodName(); ?>(new \DateTimeImmutable($entity-><?= $attribute->getPropertyName(); ?>));
<?php endif; ?>
<?php endforeach; ?>

        // Parse created/updated dates
        $entity->setCreated(new \DateTimeImmutable($entity->created));
        $entity->setUpdated(new \DateTimeImmutable($entity->updated));
        return $entity;
    }

    // Fetch/iterate multiple
    protected static function fetch<?= $entity->getPluralClassName(); ?>($result) {
        while ($entity = static::fetch<?= $entity->getClassName(); ?>($result)) {
            yield $entity;
        }
    }

    // Fetch relationships
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\OneToMany): ?>
<?php if ($entity == $relationship->getFrom()): ?>
    // Fetch one to many relationships
    public function fetch<?= $relationship->getTo()->getPluralClassName(); ?>() {
        return \<?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>::findBy<?= $entity->getClassName(); ?>Id($this->getId());
    }
    
    public function get<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> === null) {
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = iterator_to_array($this->fetch<?= $relationship->getTo()->getPluralClassName(); ?>());
        }
        return $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>;
    }

    public function set<?= $relationship->getTo()->getPluralClassName(); ?>(array $entities) {
        $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = $entities;
        return $this;
    }
<?php endif; ?>
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
<?php if ($entity == $relationship->getFrom()): ?>
    // Fetch one to many relationships
    public function fetch<?= $relationship->getTo()->getPluralClassName(); ?>() {
        return \<?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>::findBy<?= $entity->getClassName(); ?>Id($this->getId());
    }
    
    public function get<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> === null) {
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = iterator_to_array($this->fetch<?= $relationship->getTo()->getPluralClassName(); ?>());
        }
        return $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>;
    }

    public function set<?= $relationship->getTo()->getPluralClassName(); ?>(array $entities) {
        $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = $entities;
        return $this;
    }
<?php endif; ?>
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\OneToOne): ?>
<?php if ($entity == $relationship->getFrom()): ?>
    // Fetch one to one relationships
    public function fetch<?= $relationship->getTo()->getClassName(); ?>() {
        if (!$this-><?= $relationship->getTo()->getPropertyName(); ?>) {
            $this-><?= $relationship->getTo()->getPropertyName(); ?> = \<?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>::findFirstBy<?= $entity->getClassName(); ?>Id($this->getId());
        }
        return $this-><?= $relationship->getTo()->getPropertyName(); ?>;
    }
    
<?php endif; ?>
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
<?php if ($entity == $relationship->getFrom()): ?>
    // Fetch has one <?= $relationship->getTo()->getName(); ?> relationship
    public function fetch<?= $relationship->getTo()->getClassName(); ?>() {
        if (!$this-><?= $relationship->getTo()->getPropertyName(); ?>) {
            $this-><?= $relationship->getTo()->getPropertyName(); ?> = \<?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>::findById($this->get<?= $relationship->getTo()->getClassName(); ?>Id());
        }
        return $this-><?= $relationship->getTo()->getPropertyName(); ?>;
    }
    
<?php endif; ?>
<?php else: ?>
<?php if ($entity == $relationship->getFrom()): ?>
    public function fetch<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if (!$this-><?= $relationship->getTo()->getPropertyName(); ?>) {
            $this-><?= $relationship->getTo()->getPropertyName(); ?> = \<?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>::findBy<?= $entity->getClassName(); ?>Id($this->getId());
        }
        return $this-><?= $relationship->getTo()->getPropertyName(); ?>;
    }
<?php endif; ?>
<?php if ($entity == $relationship->getTo()): ?>
    public static function findBy<?= $relationship->getFrom()->getClassName(); ?>Id($id) {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
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
<?php endif; ?>
<?php endforeach; ?>
<?php if ($entity->hasAuthentication()): ?>

    // Authentication methods
    public function hashPassword($password) {
        $this->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));
        return $this;
    }

    public static function validateLogin($emailAddress, $password) {
        if (!$emailAddress || !$password) {
            return null;
        }
        $entities = iterator_to_array(static::findByEmailAddress($emailAddress));
        if (count($entities) !== 1) {
            return null;
        }
        $entity = $entities[0];

        if (password_verify($password, $entity->getPasswordHash())) {
            if (password_needs_rehash($entity->getPasswordHash(), PASSWORD_DEFAULT)) {
                $entity->hashPassword($password);
                $entity->save();
            }
            return $entity;
        }
        return null;
    }

    public function login(\DateTime $expire) {
        try {
            $this->query('
                DELETE FROM <?= $entity->getTableName(); ?>_sessions
                WHERE expire < UTC_TIMESTAMP();
            ');
        } catch (\Exception $exception) {
            // Ignore (dead lock, back off try again)
        }

        $token = base64_encode(openssl_random_pseudo_bytes(128));
        $this->query('
            INSERT INTO <?= $entity->getTableName(); ?>_sessions (
                <?= $entity->getTableName(); ?>_id,
                token,
                expire,
                created
            ) VALUES (
                :entity_id,
                :token,
                :expire,
                UTC_TIMESTAMP()
            );
        ', [
            ':entity_id' => $this->getId(),
            ':token' => $token,
            ':expire' => $expire->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
        ]);
        return $token;
    }

    public static function resume($token) {
        $result = static::query('
            SELECT <?= $entity->getTableName(); ?>_id
            FROM <?= $entity->getTableName(); ?>_sessions
            WHERE
                token = :token
            LIMIT 1;
        ', [
            ':token' => $token,
        ]);
        $entityId = $result->fetch(\PDO::FETCH_COLUMN);
        if ($entityId) {
            return static::findById($entityId);
        }
    }

    public function logout() {
        $this->query('
            DELETE FROM <?= $entity->getTableName(); ?>_sessions
            WHERE <?= $entity->getTableName(); ?>_id = :id;
        ', [
            ':id' => $this->getId(),
        ]);
    }

<?php endif; ?>
    // ID accessors
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    // Attribute accessors
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute
    || $attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>

    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?> ?: '';
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>

    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?> ?: 0;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
    public function is<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(bool $value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php endforeach; ?>
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
