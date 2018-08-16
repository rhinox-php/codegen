<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->class; ?> extends AbstractModel implements \JsonSerializable {

<?php if ($entity->hasRelationshipsByType(['HasMany'])): ?>
    // Has many related entities

<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>
    /**
     * @var \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>[]|null Array of related <?= $relationship->class; ?> instances or null if they haven't been fetched yet.
     */
    protected $<?= $relationship->getTo()->pluralProperty; ?> = null;

<?php endforeach; ?>
<?php endif; ?>

    // Has one related entities

<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>
    /**
     * @var \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>|null Related <?= $relationship->class; ?> instance or null if it hasn't been fetched yet.
     */
    protected $<?= $relationship->property; ?> = null;
<?php endforeach; ?>

    // Belongs to related entities

<?php foreach ($entity->iterateRelationshipsByType(['BelongsTo']) as $relationship): ?>
    /**
     * @var \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>|null Related <?= $relationship->class; ?> instance or null if it hasn't been fetched yet.
     */
    protected $<?= $relationship->property; ?> = null;
<?php endforeach; ?>

    // Datatable
    public static function getDataTable() {
        $table = new \Mea\DataTable\LaravelDataTable('<?= $entity->getPluralTableName(); ?>');
        $table->addAction(function($row) use($table) {
            return $table->createButton()
                ->setUrl('/<?= $entity->route; ?>/edit/'.$row['id'])
                ->setText('Edit')
                ->addClass('btn btn-default btn-xs');
        });
        $table->addColumn('id')->setHeader('ID');
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
        $table->addColumn('<?= $attribute->column; ?>')->setHeader('<?= $attribute->label; ?>');
<?php endforeach; ?>
        return $table;
    }

    // Json
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
            '<?= $attribute->property; ?>' => $this-><?= $attribute->getGetterName(); ?>(),
<?php endforeach; ?>
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated(),
        ];
    }

    // Find methods
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string') ||
        $attribute->is('int')): ?>

    // Find by attribute <?= $attribute->name; ?>

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>[] Returns an array of instances where the <?= $attribute->name; ?> attribute matches the supplied value.
     */
    public static function findBy<?= $attribute->method; ?>($value) {
        return iterator_to_array(static::where('<?= $attribute->column; ?>', $value)->get());
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?>
     */
    public static function findFirstBy<?= $attribute->method; ?>($value) {
        return static::where('<?= $attribute->column; ?>', $value)->first();
    }
<?php endif; ?>
<?php endforeach; ?>

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
<?php foreach ($entity->iterateRelationshipsByType(['HasOne']) as $relationship): ?>
        if ($this-><?= $relationship->property; ?> !== null && $this->get<?= $relationship->class; ?>Id() != $this-><?= $relationship->property; ?>->getId()) {
            $this-><?= $relationship->property; ?>->save();
            $this->set<?= $relationship->class; ?>Id($this-><?= $relationship->property; ?>->getId());
            parent::save();
        }
<?php endforeach; ?>
    }

    // Has many related accessors
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Returns the Eloquent relationship to <?= $relationship->class; ?>.
     */
    protected function <?= $relationship->getTo()->pluralProperty; ?>() {
        return $this->hasMany(\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>::class);
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>[] Returns an array of related <?= $relationship->class; ?> instances.
     */
    public function get<?= $relationship->getTo()->pluralClass; ?>() {
        if ($this-><?= $relationship->getTo()->pluralProperty; ?> === null) {
            $this-><?= $relationship->getTo()->pluralProperty; ?> = iterator_to_array($this-><?= $relationship->getTo()->pluralProperty; ?>()->get());
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne Returns the Eloquent relationship to <?= $relationship->class; ?>.
     */
    protected function <?= $relationship->property; ?>() {
        return $this->belongsTo(\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>::class);
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>|null Returns the related <?= $relationship->class; ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->class; ?>() {
        if ($this-><?= $relationship->property; ?> === null) {
            $this-><?= $relationship->property; ?> = $this-><?= $relationship->property; ?>()->first();
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Returns the Eloquent relationship to <?= $relationship->class; ?>.
     */
    protected function <?= $relationship->property; ?>() {
        return $this->belongsTo(\<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>::class);
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $relationship->getTo()->class; ?>|null Returns the related <?= $relationship->class; ?> instance, or returns null if none is assigned.
     */
    public function get<?= $relationship->class; ?>() {
        if ($this-><?= $relationship->property; ?> === null) {
            $this-><?= $relationship->property; ?> = $this-><?= $relationship->property; ?>()->first();
        }
        return $this-><?= $relationship->property; ?>;
    }

    /**
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> This instance for method chaining.
     */
    public function set<?= $relationship->class; ?>(<?= $relationship->class; ?> $<?= $relationship->property; ?>) {
        $this-><?= $relationship->property; ?> = $<?= $relationship->property; ?>;
        return $this;
    }

<?php endforeach; ?>

    // Attribute accessors
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string')
    || $attribute->is('text')): ?>
    /**
     * @return string|null Returns the <?= $attribute->name; ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->column; ?>;
    }

    /**
     * @param string|null $value Sets the <?= $attribute->name; ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> This instance for method chaining.
     */
    public function set<?= $attribute->method; ?>(string $value): self {
        $this-><?= $attribute->column; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute->is('int')): ?>
    /**
     * @return integer|null Returns the <?= $attribute->name; ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->column; ?>;
    }

    /**
     * @param integer|null $value Sets the <?= $attribute->name; ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> This instance for method chaining.
     */
    public function set<?= $attribute->method; ?>($value) {
        $this-><?= $attribute->column; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute->is('decimal')): ?>
    /**
     * @return float|null Returns the <?= $attribute->name; ?> attribute, or null if not set.
     */
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->column; ?>;
    }

    /**
     * @param float|null $value Sets the <?= $attribute->name; ?> attribute.
     * @return \<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> This instance for method chaining.
     */
    public function set<?= $attribute->method; ?>($value) {
        $this-><?= $attribute->column; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute->is('date')): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        return $this-><?= $attribute->column; ?>;
    }

    public function set<?= $attribute->method; ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->column; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php if ($attribute->is('bool')): ?>
    public function <?= $attribute->getGetterName(); ?>() {
        if ($this-><?= $attribute->column; ?> === null) {
            return null;
        }
        return (bool) $this-><?= $attribute->column; ?>;
    }

    public function set<?= $attribute->method; ?>($value) {
        $this-><?= $attribute->column; ?> = $value;
        return $this;
    }

<?php endif; ?>
<?php endforeach; ?>

}
