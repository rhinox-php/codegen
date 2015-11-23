<?= '<?php'; ?>

namespace <?= $codegen->getNamespace(); ?>\Model;

class <?= $entity->getClassName(); ?> {
    use \Rhino\Core\Model\MySqlModel;

    protected $id;
<?php foreach ($entity->getAttributes() as $attribute): ?>
    protected $<?= $attribute->getPropertyName(); ?>;
<?php endforeach; ?>
    protected $updated;
    protected $created;
    
    protected static $table = '<?= $entity->getTableName(); ?>';
    
    protected static $columns = '
        <?= $entity->getTableName(); ?>.id,
<?php foreach ($entity->getAttributes() as $attribute): ?>
        <?= $entity->getTableName(); ?>.<?= $attribute->getColumnName(); ?> AS <?= $attribute->getPropertyName(); ?>,
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

    public function save() {
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    protected function insert() {
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
                UTC_TIMESTAMP()
            );
        ', [
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>()->format('Y-m-d'),
<?php else: ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php endif; ?>
<?php endforeach; ?>
        ]);
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
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>()->format('Y-m-d'),
<?php else: ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php endif; ?>
<?php endforeach; ?>
        ]);
    }

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
    public static function findBy<?= $attribute->getMethodName(); ?>($value) {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . '
            WHERE <?= $attribute->getColumnName(); ?> = :value;
        ', [
            ':value' => $value,
        ]));
    }
<?php endif; ?>
<?php endforeach; ?>

    public static function iterateAll() {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . ';
        '));
    }

    protected static function fetch<?= $entity->getClassName(); ?>($result) {
        $entity = $result->fetchObject(static::class);
        if (!$entity) {
            return null;
        }

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
        $entity->set<?= $attribute->getMethodName(); ?>(new \DateTimeImmutable($entity-><?= $attribute->getPropertyName(); ?>));
<?php endif; ?>
<?php endforeach; ?>
        $entity->setCreated(new \DateTimeImmutable($entity->created));
        $entity->setUpdated(new \DateTimeImmutable($entity->updated));
        return $entity;
    }

    protected static function fetch<?= $entity->getPluralClassName(); ?>($result) {
        while ($entity = static::fetch<?= $entity->getClassName(); ?>($result)) {
            yield $entity;
        }
    }

<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\OneToMany): ?>
<?php if ($entity == $relationship->getFrom()): ?>
    public function fetch<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if (!$this-><?= $relationship->getTo()->getPropertyName(); ?>) {
            $this-><?= $relationship->getTo()->getPropertyName(); ?> = <?= $relationship->getTo()->getClassName(); ?>::findBy<?= $entity->getClassName(); ?>Id($this->getId());
        }
        return $this-><?= $relationship->getTo()->getPropertyName(); ?>;
    }
<?php endif; ?>
<?php else: ?>
<?php if ($entity == $relationship->getFrom()): ?>
    public function fetch<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if (!$this-><?= $relationship->getTo()->getPropertyName(); ?>) {
            $this-><?= $relationship->getTo()->getPropertyName(); ?> = <?= $relationship->getTo()->getClassName(); ?>::findBy<?= $entity->getClassName(); ?>Id($this->getId());
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

    public static function validateLogin($emailAddress, $password) {
        $entity = static::findByEmailAddress($emailAddress);

        if (!$entity) {
            return null;
        }

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

    public function logout($token, $count = 10) {
        $this->query('
            DELETE FROM <?= $entity->getTableName(); ?>_sessions
            WHERE token = :token;
        ', [
            ':token' => $token,
        ]);
    }

<?php endif; ?>
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute
    || $attribute instanceof \Rhino\Codegen\Attribute\TextAttribute
    || $attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>

    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?> ?: '';
    }

    public function set<?= $attribute->getMethodName(); ?>(string $value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(bool $value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
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
