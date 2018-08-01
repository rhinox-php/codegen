<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->getClassName(); ?> extends AbstractModel {

    protected $id;

    // Properties
<?php foreach ($entity->getAttributes() as $attribute): ?>
    protected $<?= $attribute->getPropertyName(); ?>;
<?php endforeach; ?>

    protected $created;
    protected $updated;

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

    public function __construct() {
        throw new \Exception('Generated models should not be instantiated directly.');
    }

    // Columns
    protected static $columns = '
        `<?= $entity->getTableName(); ?>`.`id`,
<?php foreach ($entity->getAttributes() as $attribute): ?>
        `<?= $entity->getTableName(); ?>`.`<?= $attribute->getColumnName(); ?>`,
<?php endforeach; ?>
        `<?= $entity->getTableName(); ?>`.`created`,
        `<?= $entity->getTableName(); ?>`.`updated`
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

    // Sync
    public static function sync(\DateTimeImmutable $since): \Generator {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                `updated` >= :since
                OR `created` >= :since;
        ', [
            ':since' => static::formatMySqlDateTime($since),
        ]));
    }

    // Json
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isType(['String', 'Text', 'Int', 'Decimal'])): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php elseif ($attribute->isType(['Bool'])): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->is<?= $attribute->getMethodName(); ?>(),
<?php elseif ($attribute->isType(['Date'])): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->get<?= $attribute->getMethodName(); ?>()->format(static::DATE_FORMAT) : null,
<?php elseif ($attribute->isType(['DateTime'])): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->get<?= $attribute->getMethodName(); ?>()->format(static::DATE_TIME_FORMAT) : null,
<?php endif; ?>
<?php endforeach; ?>
            'created' => $this->getCreated() ? $this->getCreated()->format(static::DATE_TIME_FORMAT) : null,
            'updated' => $this->getUpdated() ? $this->getUpdated()->format(static::DATE_TIME_FORMAT) : null,
        ];
    }

    // Save/insert/update/delete
    protected function insert() {
        $date = new \DateTimeImmutable('now', new \DateTimezone('UTC'));
        $this->setUpdated($date);
        $this->setCreated($date);
        $this->query('
            INSERT INTO `<?= $entity->getTableName(); ?>` (
<?php foreach ($entity->getAttributes() as $attribute): ?>
                `<?= $attribute->getColumnName(); ?>`,
<?php endforeach; ?>
                `updated`,
                `created`
            ) VALUES (
<?php foreach ($entity->getAttributes() as $attribute): ?>
                :<?= $attribute->getColumnName(); ?>,
<?php endforeach; ?>
                :updated,
                :created
            );
        ', $this->getQueryParams());

        $this->setId($this->lastInsertId());
    }

    protected function update() {
        $this->setUpdated(new \DateTimeImmutable('now', new \DateTimezone('UTC')));

        $params = $this->getQueryParams();
        $params[':id'] = $this->getId();
        $this->query('
            UPDATE `<?= $entity->getTableName(); ?>`
            SET
<?php foreach ($entity->getAttributes() as $attribute): ?>
                `<?= $attribute->getColumnName(); ?>` = :<?= $attribute->getColumnName(); ?>,
<?php endforeach; ?>
                `updated` = :updated,
                `created` = :created
            WHERE `id` = :id
            LIMIT 1;
        ', $params);

    }

    protected function getQueryParams() {
       return [
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isType(['String', 'Text', 'Int', 'Decimal'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php elseif ($attribute->isType(['Bool'])): ?>
<?php if ($attribute->isNullable()): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->is<?= $attribute->getMethodName(); ?>() === null ? null : (int) $this->is<?= $attribute->getMethodName(); ?>(),
<?php else: ?>
            ':<?= $attribute->getColumnName(); ?>' => (int) $this->is<?= $attribute->getMethodName(); ?>(),
<?php endif; ?>
<?php elseif ($attribute->isType(['Date'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->formatMySqlDate($this->get<?= $attribute->getMethodName(); ?>()) : null,
<?php elseif ($attribute->isType(['DateTime'])): ?>
            ':<?= $attribute->getColumnName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>() ? $this->formatMySqlDateTime($this->get<?= $attribute->getMethodName(); ?>()) : null,
<?php endif; ?>
<?php endforeach; ?>
			':created' => $this->formatMySqlDateTime($this->getCreated()),
			':updated' => $this->formatMySqlDateTime($this->getUpdated()),
        ];
    }

    public function delete() {
        $this->query('
            DELETE FROM `<?= $entity->getTableName(); ?>`
            WHERE `id` = :id;
        ', [
            ':id' => $this->getId(),
        ]);
    }

    public static function hydrateFromPdoStatement($statement): \Generator {
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $entity = new static();
            $entity->id = $row['id'];
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isType(['String', 'Text'])): ?>
            $entity-><?= $attribute->getPropertyName(); ?> = $row['<?= $attribute->getColumnName(); ?>'] ?? null;
<?php elseif ($attribute->isType(['Int'])): ?>
            $entity-><?= $attribute->getPropertyName(); ?> = isset($row['<?= $attribute->getColumnName(); ?>']) ? (int) $row['<?= $attribute->getColumnName(); ?>'] : null;
<?php elseif ($attribute->isType(['Decimal'])): ?>
$entity-><?= $attribute->getPropertyName(); ?> = isset($row['<?= $attribute->getColumnName(); ?>']) ? (float) $row['<?= $attribute->getColumnName(); ?>'] : null;
<?php elseif ($attribute->isType(['Bool'])): ?>
<?php if ($attribute->isNullable()): ?>
            $entity-><?= $attribute->getPropertyName(); ?> = $row['<?= $attribute->getColumnName(); ?>'] ?? null;
<?php else: ?>
            $entity-><?= $attribute->getPropertyName(); ?> = $row['<?= $attribute->getColumnName(); ?>'] ?? null;
<?php endif; ?>
<?php elseif ($attribute->isType(['Date'])): ?>
            if (isset($row['<?= $attribute->getColumnName(); ?>'])) {
                $entity-><?= $attribute->getPropertyName(); ?> = \DateTimeImmutable::createFromFormat(static::MYSQL_DATE_FORMAT, $row['<?= $attribute->getColumnName(); ?>'], new \DateTimezone('UTC'));
            }
<?php elseif ($attribute->isType(['DateTime'])): ?>
            if (isset($row['<?= $attribute->getColumnName(); ?>'])) {
                $entity-><?= $attribute->getPropertyName(); ?> = \DateTimeImmutable::createFromFormat(static::MYSQL_DATE_TIME_FORMAT, $row['<?= $attribute->getColumnName(); ?>'], new \DateTimezone('UTC'));
            }
<?php endif; ?>
<?php endforeach; ?>

            if (isset($row['created'])) {
                $entity->setCreated(\DateTimeImmutable::createFromFormat(static::MYSQL_DATE_TIME_FORMAT, $row['created'], new \DateTimezone('UTC')));
            }
            if (isset($row['updated'])) {
                $entity->setUpdated(\DateTimeImmutable::createFromFormat(static::MYSQL_DATE_TIME_FORMAT, $row['updated'], new \DateTimezone('UTC')));
            }

            $entity = static::processFetchedEntity($entity);

            if ($entity) {
                yield $entity;
            }
        }
    }

    protected function saveRelated() {
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
        $this->save<?= $relationship->getPluralClassName(); ?>();
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
        $this->save<?= $relationship->getClassName(); ?>();
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

<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
    protected function save<?= $relationship->getClassName(); ?>() {
        if ($this-><?= $relationship->getPropertyName(); ?> !== null) {
            $relatedEntity = $this-><?= $relationship->getPropertyName(); ?>;
            $relatedEntity->set<?= $relationship->getFrom()->getClassName(); ?>Id($this->getId());
            $relatedEntity->save();
            $this->query("
                DELETE FROM <?= $relationship->getTo()->getTableName(); ?>

                WHERE
                    id != ?
                    AND <?= $relationship->getFrom()->getTableName(); ?>_id = ?;
            ", [
                $relatedEntity->getId(),
                $this->getId(),
            ]);
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
            FROM `' . static::$table . '`
            WHERE id = :id;
        ', [
            ':id' => $id,
        ]));
    }
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isType(['String', 'Text', 'Int', 'Decimal'])): ?>

    // Find by attribute <?= $attribute->getName(); ?>

    public static function findBy<?= $attribute->getMethodName(); ?>($value) {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `<?= $attribute->getColumnName(); ?>` = :value;
        ', [
            ':value' => $value,
        ]));
    }

    /**
     * Find the first instance matching the supplied <?= $attribute->getName(); ?> or
     * `null` if there was no results.
     *
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>|null
     */
    public static function findFirstBy<?= $attribute->getMethodName(); ?>($value) {
        return static::fetch<?= $entity->getClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `<?= $attribute->getColumnName(); ?>` = :value
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
    }

    public static function countBy<?= $attribute->getMethodName(); ?>($value) {
        return (int) static::query('
            SELECT COUNT(`id`)
            FROM `' . static::$table . '`
            WHERE `<?= $attribute->getColumnName(); ?>` = :value;
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }
<?php endif; ?>
<?php if ($attribute->isType(['Bool'])): ?>

    // Find by attribute <?= $attribute->getName(); ?>

    public static function findBy<?= $attribute->getMethodName(); ?>($value) {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->getColumnName(); ?> = :value
                OR (:value = 0 AND <?= $attribute->getColumnName(); ?> IS NULL);
        ', [
            ':value' => $value ? 1 : 0,
        ]));
    }

    /**
     * Find the first instance matching the supplied <?= $attribute->getName(); ?> or
     * `null` if there was no results.
     *
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>|null
     */
    public static function findFirstBy<?= $attribute->getMethodName(); ?>($value) {
        return static::fetch<?= $entity->getClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->getColumnName(); ?> = :value
                OR (:value = 0 AND <?= $attribute->getColumnName(); ?> IS NULL)
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
    }

    public static function countBy<?= $attribute->getMethodName(); ?>($value) {
        return (int) static::query('
            SELECT COUNT(id)
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->getColumnName(); ?> = :value
                OR (:value = 0 AND <?= $attribute->getColumnName(); ?> IS NULL);
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }
<?php endif; ?>
<?php if ($attribute->isType(['Date', 'DateTime'])): ?>

    public static function findBy<?= $attribute->getMethodName(); ?>Before($value) {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->getColumnName(); ?> < :value
                OR <?= $attribute->getColumnName(); ?> IS NULL
        ', [
            ':value' => static::formatMySqlDateTime($value),
        ]));
    }

    public static function findBy<?= $attribute->getMethodName(); ?>After($value) {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->getColumnName(); ?> > :value
        ', [
            ':value' => static::formatMySqlDateTime($value),
        ]));
    }
<?php endif; ?>
<?php endforeach; ?>

    /**
     * Yields an instance for every row stored in the database.
     *
     * WARNING: It is not advisable to use this method on tables with many rows
     * as it will likely be quite slow.
     *
     * @return Generator|\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>[]
     */
    public static function iterateAll(): \Generator {
        return static::fetch<?= $entity->getPluralClassName(); ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`;
        '));
    }

    /**
     * Gets the count of the total number of rows in the table.
     *
     * WARNING: May be slow depending on database engine and amount of rows.
     */
    public static function countAll(): int {
        return static::query('
            SELECT COUNT(*) AS count
            FROM `' . static::$table . '`;
        ')->fetchColumn();
    }

    /**
     * Returns an array of every instance stored in the database.
     *
     * WARNING: This method can quickly cause a out of memory error if there are
     * many rows in the database.
     *
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>[]
     */
    public static function getAll() {
        return iterator_to_array(static::iterateAll());
    }

    /**
     * Fetch a single instance of <?= $entity->getClassName(); ?> from a PDO result,
     * or `null` if there was no results.
     *
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>|null
     */
    protected static function fetch<?= $entity->getClassName(); ?>(\PDOStatement $result) {
        foreach (static::hydrateFromPdoStatement($result) as $entity) {
            return $entity;
        }
        return null;
    }

    /**
     * Yield multiple instances of <?= $entity->getClassName(); ?> from a PDO result.
     *
     * @return \Generator|\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>[]
     */
    protected static function fetch<?= $entity->getPluralClassName(); ?>(\PDOStatement $result): \Generator {
        return static::hydrateFromPdoStatement($result);
    }

    // Fetch relationships
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
    // Fetch has many <?= $relationship->getTo()->getName(); ?> relationships as <?= $relationship->getClassName(); ?>

    /**
     * Yields all related <?= $relationship->getTo()->getClassName(); ?>.
     *
     * @return \Generator|\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>[]
     */
    public function fetch<?= $relationship->getPluralMethodName(); ?>(): \Generator {
        return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>::findBy<?= $entity->getClassName(); ?>Id($this->getId());
    }

    /**
     * Returns an array of all related <?= $relationship->getTo()->getClassName(); ?>,
     * and caches the fetch call into a property.
     *
     * @return <?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>[]
     */
    public function get<?= $relationship->getPluralMethodName(); ?>() {
        if ($this-><?= $relationship->getPluralPropertyName(); ?> === null) {
            $this-><?= $relationship->getPluralPropertyName(); ?> = iterator_to_array($this->fetch<?= $relationship->getPluralMethodName(); ?>());
        }
        return $this-><?= $relationship->getPluralPropertyName(); ?>;
    }

    public function set<?= $relationship->getPluralMethodName(); ?>(array $entities) {
        $this-><?= $relationship->getPluralPropertyName(); ?> = $entities;
        return $this;
    }

<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
    // Fetch has one <?= $relationship->getTo()->getName(); ?> relationship as <?= $relationship->getClassName(); ?>

    public function fetch<?= $relationship->getMethodName(); ?>() {
        return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>::findFirstBy<?= $entity->getClassName(); ?>Id($this->getId());
    }

    public function get<?= $relationship->getMethodName(); ?>() {
        if (!$this-><?= $relationship->getPropertyName(); ?>) {
            $this-><?= $relationship->getPropertyName(); ?> = $this->fetch<?= $relationship->getMethodName(); ?>();
        }
        return $this-><?= $relationship->getPropertyName(); ?>;
    }

    public function has<?= $relationship->getMethodName(); ?>() {
        return $this->get<?= $relationship->getMethodName(); ?>() ? true : false;
    }

    public function set<?= $relationship->getMethodName(); ?>($entity) {
        $this-><?= $relationship->getPropertyName(); ?> = $entity;
        return $this;
    }

<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\BelongsTo): ?>
    // Fetch belongs to <?= $relationship->getTo()->getName(); ?> relationship as <?= $relationship->getClassName(); ?>

    public function fetch<?= $relationship->getClassName(); ?>() {
        return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>::findById($this->get<?= $relationship->getClassName(); ?>Id());
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

    public function login(\DateTimeInterface $expire) {
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
            ':expire' => $expire->setTimezone(new \DateTimeZone('UTC'))->format(static::MYSQL_DATE_TIME_FORMAT),
        ]);
        return $token;
    }

    public static function resume($token, \DateTimeInterface $expire) {
        $result = static::query('
            SELECT <?= $entity->getTableName(); ?>_id
            FROM <?= $entity->getTableName(); ?>_sessions
            WHERE
                token = :token
                AND expire > :time
            LIMIT 1;
        ', [
            ':token' => $token,
            ':time' => (new \DateTime())->setTimezone(new \DateTimeZone('UTC'))->format(static::MYSQL_DATE_TIME_FORMAT),
        ]);
        $entityId = $result->fetch(\PDO::FETCH_COLUMN);
        if ($entityId) {
            try {
                static::query('
                    UPDATE <?= $entity->getTableName(); ?>_sessions
                    SET expire = :expire
                    WHERE token = :token;
                ', [
                    ':token' => $token,
                    ':expire' => $expire->setTimezone(new \DateTimeZone('UTC'))->format(static::MYSQL_DATE_TIME_FORMAT),
                ]);
            } catch (\Exception $exception) {
                // Ignore (dead lock, back off try again)
            }
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
<?php if ($attribute->isType(['String', 'Text', 'Int', 'Decimal'])): ?>

    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php elseif ($attribute->isType(['Bool'])): ?>

    public function is<?= $attribute->getMethodName(); ?>() {
<?php if ($attribute->isNullable()): ?>
        if ($this-><?= $attribute->getPropertyName(); ?> === null) {
            return null;
        }
<?php endif; ?>
        return $this-><?= $attribute->getPropertyName(); ?> ? true : false;
    }

    public function set<?= $attribute->getMethodName(); ?>(bool $value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php elseif ($attribute->isType(['Date', 'DateTime'])): ?>

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
