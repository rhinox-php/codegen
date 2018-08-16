<?= '<?php'; ?>

namespace <?= $this->getNamespace(); ?>;

class <?= $entity->class; ?> extends AbstractModel implements \JsonSerializable {

    // Attributes
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
    protected $<?= $attribute->property; ?>;
<?php endforeach; ?>

    // Has many related entities
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>
    protected $<?= $relationship->getTo()->pluralProperty; ?> = null;
<?php endforeach; ?>

    // Has one related entities
<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>
    protected $<?= $relationship->property; ?> = null;
<?php endforeach; ?>

    // Belongs to related entities
<?php foreach ($entity->iterateRelationshipsByType(['BelongsTo']) as $relationship): ?>
    protected $<?= $relationship->property; ?> = null;
<?php endforeach; ?>

    public static function getTableName() {
        return '<?= $this->getTableNamePrefix(); ?><?= $entity->getPluralTableName(); ?>';
    }

    // Datatable
    public static function getDataTable() {
        $table = new \Mea\DataTable\DynamoDbDataTable(static::getTableName(), static::getDynamoDbClient());
        $table->addButton('create')->setUrl('/admin/<?= $entity->route; ?>/create')->setText('Create')->addClass('btn-primary');
        $table->addAction(function($row) use($table) {
            return $table->createButton()
                ->setUrl('/admin/<?= $entity->route; ?>/edit/'.$row['id'])
                ->setText('Edit')
                ->addClass('btn btn-default btn-xs');
        })->setHeader('');
        $table->addColumn('id')->setHeader('ID');
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
        $table->addColumn('<?= $attribute->property; ?>')->setHeader('<?= $attribute->label; ?>');
<?php endforeach; ?>
        return $table;
    }

    // Json
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if (!$attribute->getJsonSerialize()) continue; ?>
            '<?= $attribute->property; ?>' => $this-><?= $attribute->getGetterName(); ?>(),
<?php endforeach; ?>
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated(),
        ];
    }

    /**
     * @return string The JSON API type name.
     */
    public function getJsonApiType() {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @return mixed[] A JSON API resouce.
     */
    public function toJsonApi() {
        return [
            'id' => $this->getId(),
            'type' => $this->getJsonApiType(),
            'attributes' => [
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if (!$attribute->getJsonSerialize()) continue; ?>
<?php if ($attribute->isForeignKey()) continue; ?>
<?php if ($attribute->is('date', 'date-time')): ?>
                '<?= $attribute->property; ?>' => $this-><?= $attribute->getGetterName(); ?>() ? $this-><?= $attribute->getGetterName(); ?>()->format(DATE_ISO8601) : null,
<?php else: ?>
                '<?= $attribute->property; ?>' => $this-><?= $attribute->getGetterName(); ?>(),
<?php endif; ?>
<?php endforeach; ?>
            ],
        ];
    }

    public function iterateJsonApiIncluded()
    {
<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>
        yield <?= $relationship->property; ?> => $this->get<?= $relationship->getTo()->class; ?>();
<?php endforeach; ?>
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>
        yield <?= $relationship->pluralProperty; ?> => $this->get<?= $relationship->getTo()->pluralClass; ?>();
<?php endforeach; ?>
    }

    // Find methods

    protected static function fetchInstance(array $item) {
        $instance = new static();
        $instance->setId($item['id']['N']);

<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('date', 'date-time')): ?>
        if (isset($item['<?= $attribute->property; ?>']['<?= $this->getAttributeType($attribute); ?>'])) {
            $instance->set<?= $attribute->method; ?>(new \DateTimeImmutable($item['<?= $attribute->property; ?>']['<?= $this->getAttributeType($attribute); ?>']));
        }
<?php else: ?>
        if (isset($item['<?= $attribute->property; ?>']['<?= $this->getAttributeType($attribute); ?>'])) {
            $instance->set<?= $attribute->method; ?>($item['<?= $attribute->property; ?>']['<?= $this->getAttributeType($attribute); ?>']);
        }
<?php endif; ?>

<?php endforeach; ?>
        return $instance;
    }

    public static function findById($id) {
        $result = static::getDynamoDbClient()->getItem([
            'ConsistentRead' => true,
            'TableName' => static::getTableName(),
            'Key' => [
                'id' => ['N' => $id],
            ],
        ]);
        return static::fetchInstance($result['Item']);
    }

    // Find methods
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string') ||
        $attribute->is('int')): ?>

    // Find by attribute <?= $attribute->name; ?>

    public static function iterateBy<?= $attribute->method; ?>($value) {
        $response = static::getDynamoDbClient()->scan([
            'TableName' => static::getTableName(),
            'ExpressionAttributeValues' => [
                ':<?= $attribute->property; ?>' => ['<?= $this->getAttributeType($attribute); ?>' => $value],
            ],
            'FilterExpression' => '#<?= $attribute->property; ?> = :<?= $attribute->property; ?>',
            'ExpressionAttributeNames' => [
                '#<?= $attribute->property; ?>' => '<?= $attribute->property; ?>',
            ],
        ]);

        foreach ($response['Items'] as $item) {
            $instance = static::fetchInstance($item);
            yield $instance->getId() => $instance;
        }
    }

    public static function findBy<?= $attribute->method; ?>($value) {
        return iterator_to_array(static::iterateBy<?= $attribute->method; ?>($value));
    }

    public static function findFirstBy<?= $attribute->method; ?>($value) {
        $response = static::getDynamoDbClient()->scan([
            'TableName' => static::getTableName(),
            'ExpressionAttributeValues' => [
                ':<?= $attribute->property; ?>' => ['<?= $this->getAttributeType($attribute); ?>' => $value],
            ],
            'FilterExpression' => '#<?= $attribute->property; ?> = :<?= $attribute->property; ?>',
            'ExpressionAttributeNames' => [
                '#<?= $attribute->property; ?>' => '<?= $attribute->property; ?>',
            ],
        ]);

        foreach ($response['Items'] as $item) {
            return static::fetchInstance($item);
        }
        return null;
    }
<?php endif; ?>
<?php endforeach; ?>

    // Save
    public function save() {
        if ($this->getId()) {
            $this->update();
        } else {
            $this->insert();
        }
        $this->saveRelated();
    }

    public function insert() {
        $this->setId($this->generateId());

        $attributes = [
            'id' => ['N' => $this->getId()],
        ];

<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('date', 'date-time')): ?>
        if ($this-><?= $attribute->getGetterName(); ?>() !== null) {
            $attributes['<?= $attribute->property; ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()->format(DATE_ISO8601)];
        }
<?php elseif ($attribute->is('string', 'text')): ?>
        if ($this-><?= $attribute->getGetterName(); ?>()) {
            $attributes['<?= $attribute->property; ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()];
        }
<?php else: ?>
        if ($this-><?= $attribute->getGetterName(); ?>() !== null) {
            $attributes['<?= $attribute->property; ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()];
        }
<?php endif; ?>

<?php endforeach; ?>

        $request = [
            'TableName' => $thistable,
            'Item' => $attributes,
        ];

        // d($request, json_encode($request, JSON_PRETTY_PRINT));

        $result = $this->getDynamoDbClient()->putItem($request);
    }

    public function update() {
        $expressionAttributeValues = [];
        $setExpressions = [];
        $removeExpressions = [];
        $expressionAttributeNames = [];
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $i => $attribute): ?>
        if ($this-><?= $attribute->getGetterName(); ?>() !== null) {
<?php if ($attribute->is('date', 'date-time')): ?>
            $expressionAttributeValues[':<?= $attribute->property; ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()->format(DATE_ISO8601)];
            $setExpressions[] = '#<?= $attribute->property; ?> = :<?= $attribute->property; ?>';
<?php elseif ($attribute->is('string', 'text')): ?>
            if ($this-><?= $attribute->getGetterName(); ?>()) {
                $expressionAttributeValues[':<?= $attribute->property; ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()];
                $setExpressions[] = '#<?= $attribute->property; ?> = :<?= $attribute->property; ?>';
            } else {
                $removeExpressions[] = '#<?= $attribute->property; ?>';
            }
<?php else: ?>
            $expressionAttributeValues[':<?= $attribute->property; ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()];
            $setExpressions[] = '#<?= $attribute->property; ?> = :<?= $attribute->property; ?>';
<?php endif; ?>
            $expressionAttributeNames['#<?= $attribute->property; ?>'] = '<?= $attribute->property; ?>';
        }

<?php endforeach; ?>

        $updateExpression = [];
        if (!empty($setExpressions)) {
            $updateExpression[] = 'set ' . implode(', ', $setExpressions);
        }
        if (!empty($removeExpressions)) {
            $updateExpression[] = 'remove ' . implode(', ', $removeExpressions);
        }
        if (empty($updateExpression)) {
            return;
        }
        $updateExpression = implode(' ', $updateExpression);

        $request = [
            'TableName' => $thistable,
            'Key' => [
                'id' => ['N' => $this->getId()],
            ],
            'ExpressionAttributeValues' => $expressionAttributeValues,
            'UpdateExpression' => $updateExpression,
            'ExpressionAttributeNames' => $expressionAttributeNames,
        ];

        // d($request, json_encode($request, JSON_PRETTY_PRINT));

        $response = $this->getDynamoDbClient()->updateItem($request);
    }

    public function saveRelated()
    {
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>
        if ($this-><?= $relationship->pluralProperty; ?> !== null) {
            foreach ($this-><?= $relationship->pluralProperty; ?> as $<?= $relationship->property; ?>) {
                $<?= $relationship->property; ?>->set<?= $entity->class; ?>Id($this->getId());
                $<?= $relationship->property; ?>->save();
            }
        }
<?php endforeach; ?>
    }

    // Has many related accessors
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>[] Returns an array of related <?= $relationship->class; ?> instances.
     */
    public function get<?= $relationship->getTo()->pluralClass; ?>() {
        if ($this-><?= $relationship->getTo()->pluralProperty; ?> === null) {
            if (!$this->getId()) {
                return [];
            }
            $this-><?= $relationship->getTo()->pluralProperty; ?> = <?= $relationship->class; ?>::findBy<?= $entity->class; ?>Id($this->getId());
        }
        return $this-><?= $relationship->getTo()->pluralProperty; ?>;
    }

    /**
     * @param integer $id The ID of the <?= $relationship->class; ?> instance to find.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>|null Returns a related <?= $relationship->class; ?> instance matching the supplied ID.
     */
    public function get<?= $relationship->getTo()->class; ?>ById($id) {
        foreach ($this->get<?= $relationship->getTo()->pluralClass; ?>() as $<?= $relationship->getTo()->property; ?>) {
            if ($<?= $relationship->getTo()->property; ?>->getId() == $id) {
                return $<?= $relationship->getTo()->property; ?>;
            }
        }
        return null;
    }

    /**
     * @param \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>[] $<?= $relationship->getTo()->pluralProperty; ?> An array of <?= $relationship->class; ?> instances to associate to this <?= $entity->class; ?> instance.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> This instance for method chaining.
     */
    public function set<?= $relationship->getTo()->pluralClass; ?>(array $<?= $relationship->getTo()->pluralProperty; ?>) {
        $this-><?= $relationship->getTo()->pluralProperty; ?> = $<?= $relationship->getTo()->pluralProperty; ?>;
        return $this;
    }

<?php endforeach; ?>

    // Has one related accessors
<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>|null Returns the related <?= $relationship->class; ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->class; ?>() {
        if ($this-><?= $relationship->property; ?> === null) {
            $this-><?= $relationship->property; ?> = <?= $relationship->class; ?>::findFirstBy<?= $entity->class; ?>Id($this->getId());
        }
        return $this-><?= $relationship->property; ?>;
    }

    public function set<?= $relationship->class; ?>(\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?> $<?= $relationship->property; ?> = null) {
        $this-><?= $relationship->property; ?> = $<?= $relationship->property; ?>;
        return $this;
    }

<?php endforeach; ?>

    // Belongs to related accessors
<?php foreach ($entity->iterateRelationshipsByType(['BelongsTo']) as $relationship): ?>

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>|null Returns the related <?= $relationship->class; ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->class; ?>() {
        if ($this-><?= $relationship->property; ?> === null) {
            $this-><?= $relationship->property; ?> = <?= $relationship->class; ?>::findById($this->get<?= $relationship->class; ?>Id());
        }
        return $this-><?= $relationship->property; ?>;
    }

    /**
     * @return int|null Returns the ID of the related <?= $relationship->class; ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->class; ?>Id() {
        return $this-><?= $relationship->property; ?>Id;
    }

    /**
     * @param $id int|null Sets the ID of the related <?= $relationship->class; ?> instance.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> This instance for method chaining.
     */
    public function set<?= $relationship->class; ?>Id($id) {
        $this-><?= $relationship->property; ?>Id = $id;
        return $this;
    }

<?php endforeach; ?>

    // Attribute accessors
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if (!$attribute->hasAccessors()) continue; ?>
<?php if ($attribute->is('string')
    || $attribute->is('text')): ?>
    /**
     * @return string|null Returns the <?= $attribute->name; ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->property; ?>;
    }

    /**
     * @param string|null $value Sets the <?= $attribute->name; ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> This instance for method chaining.
     */
    public function set<?= $attribute->method; ?>($value) {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute->is('int')): ?>
    /**
     * @return integer|null Returns the <?= $attribute->name; ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->property; ?>;
    }

    /**
     * @param integer|null $value Sets the <?= $attribute->name; ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> This instance for method chaining.
     */
    public function set<?= $attribute->method; ?>($value) {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute->is('decimal')): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>($value) {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute->is('date')): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute->is('bool')): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        if ($this-><?= $attribute->property; ?> === null) {
            return null;
        }
        return (bool) $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>($value) {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php endforeach; ?>

}
