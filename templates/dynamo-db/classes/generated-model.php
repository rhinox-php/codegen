<?= '<?php'; ?>

namespace <?= $this->getNamespace(); ?>;

class <?= $entity->getClassName(); ?> extends AbstractModel implements \JsonSerializable {

    // Attributes
<?php foreach ($entity->getAttributes() as $attribute): ?>
    protected $<?= $attribute->getPropertyName(); ?>;
<?php endforeach; ?>

    // Has many related entities
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>
    protected $<?= $relationship->getTo()->getPluralPropertyName(); ?> = null;
<?php endforeach; ?>

    // Has one related entities
<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>
    protected $<?= $relationship->getPropertyName(); ?> = null;
<?php endforeach; ?>

    // Belongs to related entities
<?php foreach ($entity->iterateRelationshipsByType(['BelongsTo']) as $relationship): ?>
    protected $<?= $relationship->getPropertyName(); ?> = null;
<?php endforeach; ?>

    public static function getTableName() {
        return '<?= $this->getTableNamePrefix(); ?><?= $entity->getPluralTableName(); ?>';
    }

    // Datatable
    public static function getDataTable() {
        $table = new \Mea\DataTable\DynamoDbDataTable(static::getTableName(), static::getDynamoDbClient());
        $table->addButton('create')->setUrl('/admin/<?= $entity->getRouteName(); ?>/create')->setText('Create')->addClass('btn-primary');
        $table->addAction(function($row) use($table) {
            return $table->createButton()
                ->setUrl('/admin/<?= $entity->getRouteName(); ?>/edit/'.$row['id'])
                ->setText('Edit')
                ->addClass('btn btn-default btn-xs');
        })->setHeader('');
        $table->addColumn('id')->setHeader('ID');
<?php foreach ($entity->getAttributes() as $attribute): ?>
        $table->addColumn('<?= $attribute->getPropertyName(); ?>')->setHeader('<?= $attribute->getLabel(); ?>');
<?php endforeach; ?>
        return $table;
    }

    // Json
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if (!$attribute->getJsonSerialize()) continue; ?>
            '<?= $attribute->getPropertyName(); ?>' => $this-><?= $attribute->getGetterName(); ?>(),
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
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if (!$attribute->getJsonSerialize()) continue; ?>
<?php if ($attribute->isForeignKey()) continue; ?>
<?php if ($attribute->isType(['Date', 'DateTime'])): ?>
                '<?= $attribute->getPropertyName(); ?>' => $this-><?= $attribute->getGetterName(); ?>() ? $this-><?= $attribute->getGetterName(); ?>()->format(DATE_ISO8601) : null,
<?php else: ?>
                '<?= $attribute->getPropertyName(); ?>' => $this-><?= $attribute->getGetterName(); ?>(),
<?php endif; ?>
<?php endforeach; ?>
            ],
        ];
    }

    public function iterateJsonApiIncluded()
    {
<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>
        yield <?= $relationship->getPropertyName(); ?> => $this->get<?= $relationship->getTo()->getClassName(); ?>();
<?php endforeach; ?>
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>
        yield <?= $relationship->getPluralPropertyName(); ?> => $this->get<?= $relationship->getTo()->getPluralClassName(); ?>();
<?php endforeach; ?>
    }

    // Find methods

    protected static function fetchInstance(array $item) {
        $instance = new static();
        $instance->setId($item['id']['N']);

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isType(['Date', 'DateTime'])): ?>
        if (isset($item['<?= $attribute->getPropertyName(); ?>']['<?= $this->getAttributeType($attribute); ?>'])) {
            $instance->set<?= $attribute->getMethodName(); ?>(new \DateTimeImmutable($item['<?= $attribute->getPropertyName(); ?>']['<?= $this->getAttributeType($attribute); ?>']));
        }
<?php else: ?>
        if (isset($item['<?= $attribute->getPropertyName(); ?>']['<?= $this->getAttributeType($attribute); ?>'])) {
            $instance->set<?= $attribute->getMethodName(); ?>($item['<?= $attribute->getPropertyName(); ?>']['<?= $this->getAttributeType($attribute); ?>']);
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
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute ||
        $attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>

    // Find by attribute <?= $attribute->getName(); ?>

    public static function iterateBy<?= $attribute->getMethodName(); ?>($value) {
        $response = static::getDynamoDbClient()->scan([
            'TableName' => static::getTableName(),
            'ExpressionAttributeValues' => [
                ':<?= $attribute->getPropertyName(); ?>' => ['<?= $this->getAttributeType($attribute); ?>' => $value],
            ],
            'FilterExpression' => '#<?= $attribute->getPropertyName(); ?> = :<?= $attribute->getPropertyName(); ?>',
            'ExpressionAttributeNames' => [
                '#<?= $attribute->getPropertyName(); ?>' => '<?= $attribute->getPropertyName(); ?>',
            ],
        ]);

        foreach ($response['Items'] as $item) {
            $instance = static::fetchInstance($item);
            yield $instance->getId() => $instance;
        }
    }

    public static function findBy<?= $attribute->getMethodName(); ?>($value) {
        return iterator_to_array(static::iterateBy<?= $attribute->getMethodName(); ?>($value));
    }

    public static function findFirstBy<?= $attribute->getMethodName(); ?>($value) {
        $response = static::getDynamoDbClient()->scan([
            'TableName' => static::getTableName(),
            'ExpressionAttributeValues' => [
                ':<?= $attribute->getPropertyName(); ?>' => ['<?= $this->getAttributeType($attribute); ?>' => $value],
            ],
            'FilterExpression' => '#<?= $attribute->getPropertyName(); ?> = :<?= $attribute->getPropertyName(); ?>',
            'ExpressionAttributeNames' => [
                '#<?= $attribute->getPropertyName(); ?>' => '<?= $attribute->getPropertyName(); ?>',
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

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isType(['Date', 'DateTime'])): ?>
        if ($this-><?= $attribute->getGetterName(); ?>() !== null) {
            $attributes['<?= $attribute->getPropertyName(); ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()->format(DATE_ISO8601)];
        }
<?php elseif ($attribute->isType(['String', 'Text'])): ?>
        if ($this-><?= $attribute->getGetterName(); ?>()) {
            $attributes['<?= $attribute->getPropertyName(); ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()];
        }
<?php else: ?>
        if ($this-><?= $attribute->getGetterName(); ?>() !== null) {
            $attributes['<?= $attribute->getPropertyName(); ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()];
        }
<?php endif; ?>

<?php endforeach; ?>

        $request = [
            'TableName' => $this->getTableName(),
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
<?php foreach ($entity->getAttributes() as $i => $attribute): ?>
        if ($this-><?= $attribute->getGetterName(); ?>() !== null) {
<?php if ($attribute->isType(['Date', 'DateTime'])): ?>
            $expressionAttributeValues[':<?= $attribute->getPropertyName(); ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()->format(DATE_ISO8601)];
            $setExpressions[] = '#<?= $attribute->getPropertyName(); ?> = :<?= $attribute->getPropertyName(); ?>';
<?php elseif ($attribute->isType(['String', 'Text'])): ?>
            if ($this-><?= $attribute->getGetterName(); ?>()) {
                $expressionAttributeValues[':<?= $attribute->getPropertyName(); ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()];
                $setExpressions[] = '#<?= $attribute->getPropertyName(); ?> = :<?= $attribute->getPropertyName(); ?>';
            } else {
                $removeExpressions[] = '#<?= $attribute->getPropertyName(); ?>';
            }
<?php else: ?>
            $expressionAttributeValues[':<?= $attribute->getPropertyName(); ?>'] = ['<?= $this->getAttributeType($attribute); ?>' => $this-><?= $attribute->getGetterName(); ?>()];
            $setExpressions[] = '#<?= $attribute->getPropertyName(); ?> = :<?= $attribute->getPropertyName(); ?>';
<?php endif; ?>
            $expressionAttributeNames['#<?= $attribute->getPropertyName(); ?>'] = '<?= $attribute->getPropertyName(); ?>';
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
            'TableName' => $this->getTableName(),
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
        if ($this-><?= $relationship->getPluralPropertyName(); ?> !== null) {
            foreach ($this-><?= $relationship->getPluralPropertyName(); ?> as $<?= $relationship->getPropertyName(); ?>) {
                $<?= $relationship->getPropertyName(); ?>->set<?= $entity->getClassName(); ?>Id($this->getId());
                $<?= $relationship->getPropertyName(); ?>->save();
            }
        }
<?php endforeach; ?>
    }

    // Has many related accessors
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>[] Returns an array of related <?= $relationship->getClassName(); ?> instances.
     */
    public function get<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> === null) {
            if (!$this->getId()) {
                return [];
            }
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = <?= $relationship->getClassName(); ?>::findBy<?= $entity->getClassName(); ?>Id($this->getId());
        }
        return $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>;
    }

    /**
     * @param integer $id The ID of the <?= $relationship->getClassName(); ?> instance to find.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>|null Returns a related <?= $relationship->getClassName(); ?> instance matching the supplied ID.
     */
    public function get<?= $relationship->getTo()->getClassName(); ?>ById($id) {
        foreach ($this->get<?= $relationship->getTo()->getPluralClassName(); ?>() as $<?= $relationship->getTo()->getPropertyName(); ?>) {
            if ($<?= $relationship->getTo()->getPropertyName(); ?>->getId() == $id) {
                return $<?= $relationship->getTo()->getPropertyName(); ?>;
            }
        }
        return null;
    }

    /**
     * @param \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>[] $<?= $relationship->getTo()->getPluralPropertyName(); ?> An array of <?= $relationship->getClassName(); ?> instances to associate to this <?= $entity->getClassName(); ?> instance.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> This instance for method chaining.
     */
    public function set<?= $relationship->getTo()->getPluralClassName(); ?>(array $<?= $relationship->getTo()->getPluralPropertyName(); ?>) {
        $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = $<?= $relationship->getTo()->getPluralPropertyName(); ?>;
        return $this;
    }

<?php endforeach; ?>

    // Has one related accessors
<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>|null Returns the related <?= $relationship->getClassName(); ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->getClassName(); ?>() {
        if ($this-><?= $relationship->getPropertyName(); ?> === null) {
            $this-><?= $relationship->getPropertyName(); ?> = <?= $relationship->getClassName(); ?>::findFirstBy<?= $entity->getClassName(); ?>Id($this->getId());
        }
        return $this-><?= $relationship->getPropertyName(); ?>;
    }

    public function set<?= $relationship->getClassName(); ?>(\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?> $<?= $relationship->getPropertyName(); ?> = null) {
        $this-><?= $relationship->getPropertyName(); ?> = $<?= $relationship->getPropertyName(); ?>;
        return $this;
    }

<?php endforeach; ?>

    // Belongs to related accessors
<?php foreach ($entity->iterateRelationshipsByType(['BelongsTo']) as $relationship): ?>

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>|null Returns the related <?= $relationship->getClassName(); ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->getClassName(); ?>() {
        if ($this-><?= $relationship->getPropertyName(); ?> === null) {
            $this-><?= $relationship->getPropertyName(); ?> = <?= $relationship->getClassName(); ?>::findById($this->get<?= $relationship->getClassName(); ?>Id());
        }
        return $this-><?= $relationship->getPropertyName(); ?>;
    }

    /**
     * @return int|null Returns the ID of the related <?= $relationship->getClassName(); ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->getClassName(); ?>Id() {
        return $this-><?= $relationship->getPropertyName(); ?>Id;
    }

    /**
     * @param $id int|null Sets the ID of the related <?= $relationship->getClassName(); ?> instance.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> This instance for method chaining.
     */
    public function set<?= $relationship->getClassName(); ?>Id($id) {
        $this-><?= $relationship->getPropertyName(); ?>Id = $id;
        return $this;
    }

<?php endforeach; ?>

    // Attribute accessors
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if (!$attribute->hasAccessors()) continue; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute
    || $attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
    /**
     * @return string|null Returns the <?= $attribute->getName(); ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    /**
     * @param string|null $value Sets the <?= $attribute->getName(); ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> This instance for method chaining.
     */
    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
    /**
     * @return integer|null Returns the <?= $attribute->getName(); ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    /**
     * @param integer|null $value Sets the <?= $attribute->getName(); ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> This instance for method chaining.
     */
    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DecimalAttribute): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        if ($this-><?= $attribute->getPropertyName(); ?> === null) {
            return null;
        }
        return (bool) $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php endforeach; ?>

}
