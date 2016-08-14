<?= '<?php'; ?>

namespace <?= $this->getModelNamespace(); ?>;

class <?= $entity->getClassName(); ?> extends AbstractModel {
    
    // Properties
<?php foreach ($entity->getAttributes() as $attribute): ?>
    protected $<?= $attribute->getPropertyName(); ?>;
<?php endforeach; ?>

    //Related entities
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
    protected $<?= $relationship->getPluralPropertyName(); ?> = null;
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne || $relationship instanceof \Rhino\Codegen\Relationship\BelongsTo): ?>
    protected $<?= $relationship->getPropertyName(); ?> = null;
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
<?php if ($attribute->is(['String', 'Text', 'Int', 'Decimal'])): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php elseif ($attribute->is(['Bool'])): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->is<?= $attribute->getMethodName(); ?>(),
<?php elseif ($attribute->is(['Date'])): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->get<?= $attribute->getMethodName(); ?>()->format(static::DATE_FORMAT) : null,
<?php elseif ($attribute->is(['DateTime'])): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->get<?= $attribute->getMethodName(); ?>()->format(static::DATE_TIME_FORMAT) : null,
<?php endif; ?>
<?php endforeach; ?>
            'created' => $this->getCreated() ? $this->getCreated()->format(static::DATE_TIME_FORMAT) : null,
            'updated' => $this->getUpdated() ? $this->getUpdated()->format(static::DATE_TIME_FORMAT) : null,
        ];
    }

    // Save/insert/update/delete
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
<?php if ($attribute->is(['String', 'Text', 'Int', 'Decimal'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php elseif ($attribute->is(['Bool'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->is<?= $attribute->getMethodName(); ?>() ? 1 : 0,
<?php elseif ($attribute->is(['Date'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->formatMySqlDate($this->get<?= $attribute->getMethodName(); ?>()) : null,
<?php elseif ($attribute->is(['DateTime'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->formatMySqlDateTime($this->get<?= $attribute->getMethodName(); ?>()) : null,
<?php endif; ?>
<?php endforeach; ?>
            ':created' => $this->formatMySqlDateTime($this->getCreated()),
        ]);
        
        $this->setId($this->lastInsertId());
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
<?php if ($attribute->is(['String', 'Text', 'Int', 'Decimal'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php elseif ($attribute->is(['Bool'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->is<?= $attribute->getMethodName(); ?>() ? 1 : 0,
<?php elseif ($attribute->is(['Date'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->formatMySqlDate($this->get<?= $attribute->getMethodName(); ?>()) : null,
<?php elseif ($attribute->is(['DateTime'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->formatMySqlDateTime($this->get<?= $attribute->getMethodName(); ?>()) : null,
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
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
        $this->save<?= $relationship->getPluralClassName(); ?>();
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
    }
    
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
    protected function save<?= $relationship->getPluralClassName(); ?>() {
        if ($this-><?= $relationship->getPluralPropertyName(); ?> !== null) {
            if (empty($this-><?= $relationship->getPluralPropertyName(); ?>)) {
                $this->query('
                    DELETE FROM <?= $relationship->getTo()->getTableName(); ?>

                    WHERE <?= $relationship->getFrom()->getTableName(); ?>_id = ?;
                ', [$this->getId()]);
            } else {
                $deleteBindings = [];
                foreach ($this-><?= $relationship->getPluralPropertyName(); ?> as $relatedEntity) {
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
    }
    
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>

    // Find methods
    
    /**
     * @return <?= $entity->getClassName(); ?> The instance matching the ID, or null.
     */
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
        $attribute instanceof \Rhino\Codegen\Attribute\IntAttribute ||
        $attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>

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

    /**
     * Find the first instance matching the supplied <?= $attribute->getName(); ?> or
     * `null` if there was no results.
     *
     * @return \<?= $this->getModelImplementationNamespace(); ?>\<?= $entity->getClassName(); ?>|null
     */
    public static function findFirstBy<?= $attribute->getMethodName(); ?>($value) {
        return static::fetch<?= $entity->getClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . '
            WHERE <?= $attribute->getColumnName(); ?> = :value
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
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

    /**
     * Yeilds an instance for every row stored in the database.
     * 
     * WARNING: It is not advisable to use this method on tables with many rows 
     * as it will likly be quite slow.
     *
     * @return Generator|\<?= $this->getModelImplementationNamespace(); ?>\<?= $entity->getClassName(); ?>[]
     */
    public static function iterateAll() {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM ' . static::$table . ';
        '));
    }
    
    /**
     * Returns an array of every instance stored in the database.
     * 
     * WARNING: This method can quickly cause a out of memory error if there are 
     * many rows in the database.
     *
     * @return \<?= $this->getModelImplementationNamespace(); ?>\<?= $entity->getClassName(); ?>[]
     */
    public static function getAll() {
        return iterator_to_array(static::iterateAll());
    }

    /**
     * Fetch a single instance of <?= $entity->getClassName(); ?> from a PDO result,
     * or `null` if there was no results.
     *
     * @return \<?= $this->getModelImplementationNamespace(); ?>\<?= $entity->getClassName(); ?>|null
     */
    protected static function fetch<?= $entity->getClassName(); ?>(\PDOStatement $result) {
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

    /**
     * Yield multiple instances of <?= $entity->getClassName(); ?> from a PDO result.
     *
     * @return \Generator|\<?= $this->getModelImplementationNamespace(); ?>\<?= $entity->getClassName(); ?>[]
     */
    protected static function fetch<?= $entity->getPluralClassName(); ?>(\PDOStatement $result) {
        while ($entity = static::fetch<?= $entity->getClassName(); ?>($result)) {
            yield $entity;
        }
    }

    // Fetch relationships
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
    // Fetch has many <?= $relationship->getTo()->getName(); ?> relationships as <?= $relationship->getClassName(); ?>
    
    /**
     * Yields all related <?= $relationship->getTo()->getClassName(); ?>.
     *
     * @return \Generator|\<?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>[]
     */
    public function fetch<?= $relationship->getPluralClassName(); ?>() {
        return \<?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>::findBy<?= $entity->getClassName(); ?>Id($this->getId());
    }
    
    /**
     * Returns an array of all related <?= $relationship->getTo()->getClassName(); ?>, 
     * and caches the fetch call into a property.
     *
     * @return <?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>[]
     */
    public function get<?= $relationship->getPluralClassName(); ?>() {
        if ($this-><?= $relationship->getPluralPropertyName(); ?> === null) {
            $this-><?= $relationship->getPluralPropertyName(); ?> = iterator_to_array($this->fetch<?= $relationship->getPluralClassName(); ?>());
        }
        return $this-><?= $relationship->getPluralPropertyName(); ?>;
    }

    public function set<?= $relationship->getPluralClassName(); ?>(array $entities) {
        $this-><?= $relationship->getPluralPropertyName(); ?> = $entities;
        return $this;
    }
    
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne || $relationship instanceof \Rhino\Codegen\Relationship\BelongsTo): ?>
    // Fetch has one <?= $relationship->getTo()->getName(); ?> relationship as <?= $relationship->getClassName(); ?>
    
    public function fetch<?= $relationship->getClassName(); ?>() {
        return \<?= $this->getModelImplementationNamespace(); ?>\<?= $relationship->getTo()->getClassName(); ?>::findById($this->get<?= $relationship->getTo()->getClassName(); ?>Id());
    }
    
    public function get<?= $relationship->getClassName(); ?>() {
        if (!$this-><?= $relationship->getPropertyName(); ?>) {
            $this-><?= $relationship->getPropertyName(); ?> = $this->fetch<?= $relationship->getClassName(); ?>();
        }
        return $this-><?= $relationship->getPropertyName(); ?>;
    }
    
    public function has<?= $relationship->getClassName(); ?>() {
        if (!$this->get<?= $relationship->getClassName(); ?>Id()) {
            return false;
        }
        return $this->fetch<?= $relationship->getClassName(); ?>() ? true : false;
    }
    
<?php endif; ?>
<?php else: ?>
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
    
    // Attribute accessors
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String', 'Text', 'Int', 'Decimal'])): ?>

    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php elseif ($attribute->is(['Bool'])): ?>
    
    public function is<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?> ? true : false;
    }

    public function set<?= $attribute->getMethodName(); ?>(bool $value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php elseif ($attribute->is(['Date', 'DateTime'])): ?>
    
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php endforeach; ?>
    
}
