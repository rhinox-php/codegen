<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->getClassName(); ?> extends AbstractModel implements \JsonSerializable {

    // Has many related entities

<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>
    /**
     * @var \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>[]|null Array of related <?= $relationship->getClassName(); ?> instances or null if they haven't been fetched yet.
     */
    protected $<?= $relationship->getTo()->getPluralPropertyName(); ?> = null;

<?php endforeach; ?>

    // Has one related entities

<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>
    /**
     * @var \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>|null Related <?= $relationship->getClassName(); ?> instance or null if it hasn't been fetched yet.
     */
    protected $<?= $relationship->getPropertyName(); ?> = null;
<?php endforeach; ?>

    // Belongs to related entities

<?php foreach ($entity->iterateRelationshipsByType(['BelongsTo']) as $relationship): ?>
    /**
     * @var \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>|null Related <?= $relationship->getClassName(); ?> instance or null if it hasn't been fetched yet.
     */
    protected $<?= $relationship->getPropertyName(); ?> = null;
<?php endforeach; ?>

    // Datatable
    public static function getDataTable() {
        $table = new \Mea\DataTable\LaravelDataTable('<?= $entity->getPluralTableName(); ?>');
        $table->addAction(function($row) use($table) {
            return $table->createButton()
                ->setUrl('/<?= $entity->getRouteName(); ?>/edit/'.$row['id'])
                ->setText('Edit')
                ->addClass('btn btn-default btn-xs');
        });
        $table->addColumn('id')->setHeader('ID');
<?php foreach ($entity->getAttributes() as $attribute): ?>
        $table->addColumn('<?= $attribute->getColumnName(); ?>')->setHeader('<?= $attribute->getLabel(); ?>');
<?php endforeach; ?>
        return $table;
    }

    // Json
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
<?php foreach ($entity->getAttributes() as $attribute): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this-><?= $attribute->getGetterName(); ?>(),
<?php endforeach; ?>
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated(),
        ];
    }

    // Find methods
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute ||
        $attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>

    // Find by attribute <?= $attribute->getName(); ?>

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>[] Returns an array of instances where the <?= $attribute->getName(); ?> attribute matches the supplied value.
     */
    public static function findBy<?= $attribute->getMethodName(); ?>($value) {
        return iterator_to_array(static::where('<?= $attribute->getColumnName(); ?>', $value)->get());
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?>
     */
    public static function findFirstBy<?= $attribute->getMethodName(); ?>($value) {
        return static::where('<?= $attribute->getColumnName(); ?>', $value)->first();
    }
<?php endif; ?>
<?php endforeach; ?>

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
<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>
        if ($this-><?= $relationship->getPropertyName(); ?> !== null && $this->get<?= $relationship->getClassName(); ?>Id() != $this-><?= $relationship->getPropertyName(); ?>->getId()) {
            $this-><?= $relationship->getPropertyName(); ?>->save();
            $this->set<?= $relationship->getClassName(); ?>Id($this-><?= $relationship->getPropertyName(); ?>->getId());
            parent::save();
        }
<?php endforeach; ?>
    }

    // Has many related accessors
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Returns the Eloquent relationship to <?= $relationship->getClassName(); ?>.
     */
    protected function <?= $relationship->getTo()->getPluralPropertyName(); ?>() {
        return $this->hasMany(\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>::class);
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>[] Returns an array of related <?= $relationship->getClassName(); ?> instances.
     */
    public function get<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> === null) {
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = iterator_to_array($this-><?= $relationship->getTo()->getPluralPropertyName(); ?>()->get());
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne Returns the Eloquent relationship to <?= $relationship->getClassName(); ?>.
     */
    protected function <?= $relationship->getPropertyName(); ?>() {
        return $this->belongsTo(\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>::class);
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>|null Returns the related <?= $relationship->getClassName(); ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->getClassName(); ?>() {
        if ($this-><?= $relationship->getPropertyName(); ?> === null) {
            $this-><?= $relationship->getPropertyName(); ?> = $this-><?= $relationship->getPropertyName(); ?>()->first();
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Returns the Eloquent relationship to <?= $relationship->getClassName(); ?>.
     */
    protected function <?= $relationship->getPropertyName(); ?>() {
        return $this->belongsTo(\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>::class);
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->getClassName(); ?>|null Returns the related <?= $relationship->getClassName(); ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->getClassName(); ?>() {
        if ($this-><?= $relationship->getPropertyName(); ?> === null) {
            $this-><?= $relationship->getPropertyName(); ?> = $this-><?= $relationship->getPropertyName(); ?>()->first();
        }
        return $this-><?= $relationship->getPropertyName(); ?>;
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> This instance for method chaining.
     */
    public function set<?= $relationship->getClassName(); ?>(<?= $relationship->getClassName(); ?> $<?= $relationship->getPropertyName(); ?>) {
        $this-><?= $relationship->getPropertyName(); ?> = $<?= $relationship->getPropertyName(); ?>;
        return $this;
    }

<?php endforeach; ?>

    // Attribute accessors
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute
    || $attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
    /**
     * @return string|null Returns the <?= $attribute->getName(); ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    /**
     * @param string|null $value Sets the <?= $attribute->getName(); ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> This instance for method chaining.
     */
    public function set<?= $attribute->getMethodName(); ?>(string $value): self {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
    /**
     * @return integer|null Returns the <?= $attribute->getName(); ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    /**
     * @param integer|null $value Sets the <?= $attribute->getName(); ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> This instance for method chaining.
     */
    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DecimalAttribute): ?>
    /**
     * @return float|null Returns the <?= $attribute->getName(); ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    /**
     * @param float|null $value Sets the <?= $attribute->getName(); ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> This instance for method chaining.
     */
    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        if ($this-><?= $attribute->getColumnName(); ?> === null) {
            return null;
        }
        return (bool) $this-><?= $attribute->getColumnName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php endforeach; ?>

}
