<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->class; ?> extends AbstractModel {

    protected $id;

    // Properties
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
    protected $<?= $attribute->property; ?>;
<?php endforeach; ?>

    protected $created;
    protected $updated;

    //Related entities
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($relationship->is('has-many')): ?>
    protected $<?= $relationship->pluralProperty; ?> = null;
<?php endif; ?>
<?php if ($relationship->is('has-one', 'belongs-to')): ?>
    protected $<?= $relationship->property; ?> = null;
<?php endif; ?>
<?php endforeach; ?>

    // Table name
    protected static $table = '<?= $entity->table; ?>';

    public function __construct() {
        throw new \Exception('Generated models should not be instantiated directly.');
    }

    // Columns
    protected static $columns = '
        `<?= $entity->table; ?>`.`id`,
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
        `<?= $entity->table; ?>`.`<?= $attribute->column; ?>`,
<?php endforeach; ?>
        `<?= $entity->table; ?>`.`created`,
        `<?= $entity->table; ?>`.`updated`
    ';

    // Datatable
    public static function getDataTable() {
        $table = new \Rhino\DataTable\MySqlDataTable(static::getPdo(), '<?= $entity->table; ?>');
        $table->insertColumn('actions', function($column, $row) {
            // @todo fix delete button, make post, confirm
            return '
                <a href="/<?= $entity->route; ?>/edit/' . $row['id'] . '" class="btn btn-xs btn-default">Edit</a>
                <a href="/<?= $entity->route; ?>/delete/' . $row['id'] . '" class="btn btn-xs btn-link text-danger">Delete</a>
            ';
        })->setLabel('Actions');
        $table->addColumn('id')->setLabel('ID');
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
        $table->addColumn('<?= $attribute->column; ?>')->setLabel('<?= $attribute->label; ?>');
<?php endforeach; ?>
        $table->addColumn('created')->setLabel('Created');
        $table->addColumn('updated')->setLabel('Updated');
        return $table;
    }

    // Sync
    public static function sync(\DateTimeImmutable $since): \Generator {
        return static::fetch<?= $entity->pluralClass; ?>(static::query('
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
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text', 'int', 'decimal')): ?>
            '<?= $attribute->property; ?>' => $this->get<?= $attribute->method; ?>(),
<?php elseif ($attribute->is('bool')): ?>
            '<?= $attribute->property; ?>' => $this->is<?= $attribute->method; ?>(),
<?php elseif ($attribute->is('date')): ?>
            '<?= $attribute->property; ?>' => $this->get<?= $attribute->method; ?>() ? $this->get<?= $attribute->method; ?>()->format(static::DATE_FORMAT) : null,
<?php elseif ($attribute->is('date-time')): ?>
            '<?= $attribute->property; ?>' => $this->get<?= $attribute->method; ?>() ? $this->get<?= $attribute->method; ?>()->format(static::DATE_TIME_FORMAT) : null,
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
            INSERT INTO `<?= $entity->table; ?>` (
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
                `<?= $attribute->column; ?>`,
<?php endforeach; ?>
                `updated`,
                `created`
            ) VALUES (
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
                :<?= $attribute->column; ?>,
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
            UPDATE `<?= $entity->table; ?>`
            SET
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
                `<?= $attribute->column; ?>` = :<?= $attribute->column; ?>,
<?php endforeach; ?>
                `updated` = :updated,
                `created` = :created
            WHERE `id` = :id
            LIMIT 1;
        ', $params);

    }

    protected function getQueryParams() {
       return [
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text', 'int', 'decimal')): ?>
            ':<?= $attribute->column; ?>' => $this->get<?= $attribute->method; ?>(),
<?php elseif ($attribute->is('bool')): ?>
<?php if ($attribute->nullable): ?>
            ':<?= $attribute->column; ?>' => $this->is<?= $attribute->method; ?>() === null ? null : (int) $this->is<?= $attribute->method; ?>(),
<?php else: ?>
            ':<?= $attribute->column; ?>' => (int) $this->is<?= $attribute->method; ?>(),
<?php endif; ?>
<?php elseif ($attribute->is('date')): ?>
            ':<?= $attribute->column; ?>' => $this->get<?= $attribute->method; ?>() ? $this->formatMySqlDate($this->get<?= $attribute->method; ?>()) : null,
<?php elseif ($attribute->is('date-time')): ?>
            ':<?= $attribute->column; ?>' => $this->get<?= $attribute->method; ?>() ? $this->formatMySqlDateTime($this->get<?= $attribute->method; ?>()) : null,
<?php endif; ?>
<?php endforeach; ?>
			':created' => $this->formatMySqlDateTime($this->getCreated()),
			':updated' => $this->formatMySqlDateTime($this->getUpdated()),
        ];
    }

    public function delete() {
        $this->query('
            DELETE FROM `<?= $entity->table; ?>`
            WHERE `id` = :id;
        ', [
            ':id' => $this->getId(),
        ]);
    }

    public static function hydrateFromPdoStatement($statement): \Generator {
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $entity = new static();
            $entity->id = $row['id'];
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text')): ?>
            $entity-><?= $attribute->property; ?> = $row['<?= $attribute->column; ?>'] ?? null;
<?php elseif ($attribute->is('int')): ?>
            $entity-><?= $attribute->property; ?> = isset($row['<?= $attribute->column; ?>']) ? (int) $row['<?= $attribute->column; ?>'] : null;
<?php elseif ($attribute->is('decimal')): ?>
$entity-><?= $attribute->property; ?> = isset($row['<?= $attribute->column; ?>']) ? (float) $row['<?= $attribute->column; ?>'] : null;
<?php elseif ($attribute->is('bool')): ?>
<?php if ($attribute->nullable): ?>
            $entity-><?= $attribute->property; ?> = $row['<?= $attribute->column; ?>'] ?? null;
<?php else: ?>
            $entity-><?= $attribute->property; ?> = $row['<?= $attribute->column; ?>'] ?? null;
<?php endif; ?>
<?php elseif ($attribute->is('date')): ?>
            if (isset($row['<?= $attribute->column; ?>'])) {
                $entity-><?= $attribute->property; ?> = \DateTimeImmutable::createFromFormat(static::MYSQL_DATE_FORMAT, $row['<?= $attribute->column; ?>'], new \DateTimezone('UTC'));
            }
<?php elseif ($attribute->is('date-time')): ?>
            if (isset($row['<?= $attribute->column; ?>'])) {
                $entity-><?= $attribute->property; ?> = \DateTimeImmutable::createFromFormat(static::MYSQL_DATE_TIME_FORMAT, $row['<?= $attribute->column; ?>'], new \DateTimezone('UTC'));
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
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($relationship->is('has-many')): ?>
        $this->save<?= $relationship->pluralMethod; ?>();
<?php endif; ?>
<?php if ($relationship->is('has-one')): ?>
        $this->save<?= $relationship->method; ?>();
<?php endif; ?>
<?php endforeach; ?>
    }

<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($relationship->is('has-many')): ?>
    protected function save<?= $relationship->pluralMethod; ?>() {
        if ($this-><?= $relationship->pluralProperty; ?> !== null) {
            if (empty($this-><?= $relationship->pluralProperty; ?>)) {
                $this->query('
                    DELETE FROM <?= $relationship->table; ?>

                    WHERE <?= $entity->table; ?>_id = ?;
                ', [$this->getId()]);
            } else {
                $deleteBindings = [];
                foreach ($this-><?= $relationship->pluralProperty; ?> as $relatedEntity) {
                    $relatedEntity->set<?= $entity->class; ?>Id($this->getId());
                    $relatedEntity->save();
                    $deleteBindings[] = $relatedEntity->getId();
                }
                $in = implode(', ', array_fill(0, count($deleteBindings), '?'));
                $deleteBindings[] = $this->getId();
                $this->query("
                    DELETE FROM <?= $relationship->table; ?>

                    WHERE
                        id NOT IN ($in)
                        AND <?= $entity->table; ?>_id = ?;
                ", $deleteBindings);
            }
        }
    }

<?php endif; ?>
<?php if ($relationship->is('has-one')): ?>
    protected function save<?= $relationship->class; ?>() {
        if ($this-><?= $relationship->property; ?> !== null) {
            $relatedEntity = $this-><?= $relationship->property; ?>;
            $relatedEntity->set<?= $entity->class; ?>Id($this->getId());
            $relatedEntity->save();
            $this->query("
                DELETE FROM <?= $relationship->table; ?>

                WHERE
                    id != ?
                    AND <?= $entity->table; ?>_id = ?;
            ", [
                $relatedEntity->getId(),
                $this->getId(),
            ]);
        }
    }

<?php endif; ?>
<?php endforeach; ?>

    // Find methods

    /**
     * @return <?= $entity->class; ?> The instance matching the ID, or null.
     */
    public static function findById($id) {
        return static::fetch<?= $entity->class; ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE id = :id;
        ', [
            ':id' => $id,
        ]));
    }
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text', 'int', 'decimal')): ?>

    // Find by attribute <?= $attribute->name; ?>

    public static function findBy<?= $attribute->method; ?>($value) {
        return static::fetch<?= $entity->pluralClass; ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `<?= $attribute->column; ?>` = :value;
        ', [
            ':value' => $value,
        ]));
    }

    /**
     * Find the first instance matching the supplied <?= $attribute->name; ?> or
     * `null` if there was no results.
     *
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>|null
     */
    public static function findFirstBy<?= $attribute->method; ?>($value) {
        return static::fetch<?= $entity->class; ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE `<?= $attribute->column; ?>` = :value
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
    }

    public static function countBy<?= $attribute->method; ?>($value) {
        return (int) static::query('
            SELECT COUNT(`id`)
            FROM `' . static::$table . '`
            WHERE `<?= $attribute->column; ?>` = :value;
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }
<?php endif; ?>
<?php if ($attribute->is('bool')): ?>

    // Find by attribute <?= $attribute->name; ?>

    public static function findBy<?= $attribute->method; ?>($value) {
        return static::fetch<?= $entity->pluralClass; ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->column; ?> = :value
                OR (:value = 0 AND <?= $attribute->column; ?> IS NULL);
        ', [
            ':value' => $value ? 1 : 0,
        ]));
    }

    /**
     * Find the first instance matching the supplied <?= $attribute->name; ?> or
     * `null` if there was no results.
     *
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>|null
     */
    public static function findFirstBy<?= $attribute->method; ?>($value) {
        return static::fetch<?= $entity->class; ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->column; ?> = :value
                OR (:value = 0 AND <?= $attribute->column; ?> IS NULL)
            LIMIT 1;
        ', [
            ':value' => $value,
        ]));
    }

    public static function countBy<?= $attribute->method; ?>($value) {
        return (int) static::query('
            SELECT COUNT(id)
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->column; ?> = :value
                OR (:value = 0 AND <?= $attribute->column; ?> IS NULL);
        ', [
            ':value' => $value,
        ])->fetchColumn();
    }
<?php endif; ?>
<?php if ($attribute->is('date', 'date-time')): ?>

    public static function findBy<?= $attribute->method; ?>Before($value) {
        return static::fetch<?= $entity->pluralClass; ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->column; ?> < :value
                OR <?= $attribute->column; ?> IS NULL
        ', [
            ':value' => static::formatMySqlDateTime($value),
        ]));
    }

    public static function findBy<?= $attribute->method; ?>After($value) {
        return static::fetch<?= $entity->pluralClass; ?>(static::query('
            SELECT ' . static::$columns . '
            FROM `' . static::$table . '`
            WHERE
                <?= $attribute->column; ?> > :value
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
     * @return Generator|\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>[]
     */
    public static function iterateAll(): \Generator {
        return static::fetch<?= $entity->pluralClass; ?>(static::query('
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
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>[]
     */
    public static function getAll() {
        return iterator_to_array(static::iterateAll());
    }

    /**
     * Fetch a single instance of <?= $entity->class; ?> from a PDO result,
     * or `null` if there was no results.
     *
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>|null
     */
    protected static function fetch<?= $entity->class; ?>(\PDOStatement $result) {
        foreach (static::hydrateFromPdoStatement($result) as $entity) {
            return $entity;
        }
        return null;
    }

    /**
     * Yield multiple instances of <?= $entity->class; ?> from a PDO result.
     *
     * @return \Generator|\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>[]
     */
    protected static function fetch<?= $entity->pluralClass; ?>(\PDOStatement $result): \Generator {
        return static::hydrateFromPdoStatement($result);
    }

    // Fetch relationships
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($relationship->is('has-many')): ?>
    // Fetch has many <?= $relationship->entity; ?> relationships as <?= $relationship->method; ?>

    /**
     * Yields all related <?= $relationship->class; ?>.
     *
     * @return \Generator|\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->class; ?>[]
     */
    public function fetch<?= $relationship->pluralMethod; ?>(): \Generator {
        return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->class; ?>::findBy<?= $entity->class; ?>Id($this->getId());
    }

    /**
     * Returns an array of all related <?= $relationship->class; ?>,
     * and caches the fetch call into a property.
     *
     * @return <?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->class; ?>[]
     */
    public function get<?= $relationship->pluralMethod; ?>() {
        if ($this-><?= $relationship->pluralProperty; ?> === null) {
            $this-><?= $relationship->pluralProperty; ?> = iterator_to_array($this->fetch<?= $relationship->pluralMethod; ?>());
        }
        return $this-><?= $relationship->pluralProperty; ?>;
    }

    public function set<?= $relationship->pluralMethod; ?>(array $entities) {
        $this-><?= $relationship->pluralProperty; ?> = $entities;
        return $this;
    }

<?php endif; ?>
<?php if ($relationship->is('has-one')): ?>
    // Fetch has one <?= $relationship->name; ?> relationship as <?= $relationship->class; ?>

    public function fetch<?= $relationship->method; ?>() {
        return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->class; ?>::findFirstBy<?= $entity->class; ?>Id($this->getId());
    }

    public function get<?= $relationship->method; ?>() {
        if (!$this-><?= $relationship->property; ?>) {
            $this-><?= $relationship->property; ?> = $this->fetch<?= $relationship->method; ?>();
        }
        return $this-><?= $relationship->property; ?>;
    }

    public function has<?= $relationship->method; ?>() {
        return $this->get<?= $relationship->method; ?>() ? true : false;
    }

    public function set<?= $relationship->method; ?>($entity) {
        $this-><?= $relationship->property; ?> = $entity;
        return $this;
    }

<?php endif; ?>
<?php if ($relationship->is('belongs-to')): ?>
    // Fetch belongs to <?= $relationship->entity; ?> relationship as <?= $relationship->method; ?>

    public function fetch<?= $relationship->method; ?>() {
        return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->class; ?>::findById($this->get<?= $relationship->method; ?>Id());
    }

    public function get<?= $relationship->method; ?>() {
        if (!$this-><?= $relationship->property; ?>) {
            $this-><?= $relationship->property; ?> = $this->fetch<?= $relationship->method; ?>();
        }
        return $this-><?= $relationship->property; ?>;
    }

    public function has<?= $relationship->method; ?>() {
        if (!$this->get<?= $relationship->method; ?>Id()) {
            return false;
        }
        return $this->fetch<?= $relationship->method; ?>() ? true : false;
    }

<?php endif; ?>
<?php endforeach; ?>
<?php if ($entity->get('authentication')): ?>

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
                DELETE FROM <?= $entity->table; ?>_sessions
                WHERE expire < UTC_TIMESTAMP();
            ');
        } catch (\Exception $exception) {
            // Ignore (dead lock, back off try again)
        }

        $token = base64_encode(openssl_random_pseudo_bytes(128));
        $this->query('
            INSERT INTO <?= $entity->table; ?>_sessions (
                <?= $entity->table; ?>_id,
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
            SELECT <?= $entity->table; ?>_id
            FROM <?= $entity->table; ?>_sessions
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
                    UPDATE <?= $entity->table; ?>_sessions
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
            DELETE FROM <?= $entity->table; ?>_sessions
            WHERE <?= $entity->table; ?>_id = :id;
        ', [
            ':id' => $this->getId(),
        ]);
    }

<?php endif; ?>

    // Attribute accessors
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text', 'int', 'decimal')): ?>

    public function get<?= $attribute->method; ?>() {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>($value) {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }
<?php elseif ($attribute->is('bool')): ?>

    public function is<?= $attribute->method; ?>() {
<?php if ($attribute->nullable): ?>
        if ($this-><?= $attribute->property; ?> === null) {
            return null;
        }
<?php endif; ?>
        return $this-><?= $attribute->property; ?> ? true : false;
    }

    public function set<?= $attribute->method; ?>(bool $value) {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }
<?php elseif ($attribute->is('date', 'date-time')): ?>

    public function get<?= $attribute->method; ?>() {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php endforeach; ?>

}
