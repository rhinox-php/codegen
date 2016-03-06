<?= '<?php'; ?>

namespace <?= $codegen->getNamespace(); ?>\Models\Generated;

class <?= $entity->getClassName(); ?> extends \Illuminate\Database\Eloquent\Model implements \JsonSerializable {

    // Related entities
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\OneToMany): ?>
    protected $<?= $relationship->getTo()->getPluralPropertyName(); ?> = null;
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
    protected $<?= $relationship->getTo()->getPluralPropertyName(); ?> = null;
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\OneToOne): ?>
    protected $<?= $relationship->getPropertyName(); ?> = null;
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
    protected $<?= $relationship->getPropertyName(); ?> = null;
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>

    // Datatable
    public static function getDataTable() {
        $table = new \<?= $codegen->getNamespace(); ?>\DataTable\DataTable('<?= $entity->getPluralTableName(); ?>');
        $table->addColumn('id')->setHeader('ID');
<?php foreach ($entity->getAttributes() as $attribute): ?>
        $table->addColumn('<?= $attribute->getColumnName(); ?>')->setHeader('<?= $attribute->getLabel(); ?>');
<?php endforeach; ?>
        $table->addAction(function($row) use($table) {
            return $table->createButton('/<?= $entity->getRouteName(); ?>/edit/'.$row['id'], 'Edit');
        });
        return $table;
    }
    
    // Json
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
<?php foreach ($entity->getAttributes() as $attribute): ?>
            '<?= $attribute->getPropertyName(); ?>' => $this->get<?= $attribute->getMethodName(); ?>(),
<?php endforeach; ?>
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated(),
        ];
    }

    // Find methods
    public static function findById($id) {
        return static::find($id);
    }
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute ||
        $attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>

    // Find by attribute <?= $attribute->getName(); ?>

    public static function findBy<?= $attribute->getMethodName(); ?>($value) {
        return static::where('<?= $attribute->getColumnName(); ?>', $value)->get();
    }

    public static function findFirstBy<?= $attribute->getMethodName(); ?>($value) {
        return static::where('<?= $attribute->getColumnName(); ?>', $value)->first();
    }
<?php endif; ?>
<?php endforeach; ?>

    // Iterate methods
    public static function iterateAll() {
        return static::all();
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
    
    // Save 
    public function save(array $options = [])
    {
        \DB::transaction(function () {
            parent::save();
            $this->saveRelated();
        });
    }

    public function saveRelated()
    {
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\OneToMany): ?>
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> !== null) {
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>()->saveMany($this-><?= $relationship->getTo()->getPluralPropertyName(); ?>);
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>()->whereNotIn('id', array_map(function ($entity) {
                return $entity->getId();
            }, $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>))->delete();
        }
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> !== null) {
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>()->saveMany($this-><?= $relationship->getTo()->getPluralPropertyName(); ?>);
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>()->whereNotIn('id', array_map(function ($entity) {
                return $entity->getId();
            }, $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>))->delete();
        }
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\OneToOne): ?>
        if ($this-><?= $relationship->getPropertyName(); ?> !== null) {
            $this-><?= $relationship->getPropertyName(); ?>->save();
            $this->set<?= $relationship->getClassName(); ?>Id($this-><?= $relationship->getPropertyName(); ?>->getId());
            parent::save();
        }
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
        if ($this-><?= $relationship->getPropertyName(); ?> !== null) {
            $this-><?= $relationship->getPropertyName(); ?>->save();
            $this->set<?= $relationship->getClassName(); ?>Id($this-><?= $relationship->getPropertyName(); ?>->getId());
            parent::save();
        }
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
    }

    // Related accessors
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\OneToMany): ?>
    public function <?= $relationship->getTo()->getPluralPropertyName(); ?>() {
        return $this->hasMany(\<?= $codegen->getNamespace(); ?>\Models\<?= $relationship->getTo()->getClassName(); ?>::class);
    }
    
    public function get<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> === null) {
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>()->get();
        }
        return $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>;
    }
    
    public function set<?= $relationship->getTo()->getPluralClassName(); ?>(array $<?= $relationship->getTo()->getPluralPropertyName(); ?>) {
        $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = $<?= $relationship->getTo()->getPluralPropertyName(); ?>;
        return $this;
    }
    
    // @todo add<?= $relationship->getTo()->getPluralClassName(); ?>
    
    // @todo remove<?= $relationship->getTo()->getPluralClassName(); ?>
    
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
    public function <?= $relationship->getTo()->getPluralPropertyName(); ?>() {
        return $this->hasMany(\<?= $codegen->getNamespace(); ?>\Models\<?= $relationship->getTo()->getClassName(); ?>::class);
    }
    
    public function get<?= $relationship->getTo()->getPluralClassName(); ?>() {
        if ($this-><?= $relationship->getTo()->getPluralPropertyName(); ?> === null) {
            $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>()->get();
        }
        return $this-><?= $relationship->getTo()->getPluralPropertyName(); ?>;
    }
    
    public function set<?= $relationship->getTo()->getPluralClassName(); ?>(array $<?= $relationship->getTo()->getPluralPropertyName(); ?>) {
        $this-><?= $relationship->getTo()->getPluralPropertyName(); ?> = $<?= $relationship->getTo()->getPluralPropertyName(); ?>;
        return $this;
    }
    
    // @todo add<?= $relationship->getTo()->getPluralClassName(); ?>
    
    // @todo remove<?= $relationship->getTo()->getPluralClassName(); ?>
    
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\OneToOne): ?>
    public function <?= $relationship->getPropertyName(); ?>() {
        return $this->belongsTo(\<?= $codegen->getNamespace(); ?>\Models\<?= $relationship->getTo()->getClassName(); ?>::class);
    }
    
    public function get<?= $relationship->getClassName(); ?>() {
        if ($this-><?= $relationship->getPropertyName(); ?> === null) {
            $this-><?= $relationship->getPropertyName(); ?> = $this-><?= $relationship->getPropertyName(); ?>()->first();
        }
        return $this-><?= $relationship->getPropertyName(); ?>;
    }
    
    public function set<?= $relationship->getClassName(); ?>(\<?= $codegen->getNamespace(); ?>\Models\<?= $relationship->getTo()->getClassName(); ?> $<?= $relationship->getPropertyName(); ?> = null) {
        $this-><?= $relationship->getPropertyName(); ?> = $<?= $relationship->getPropertyName(); ?>;
        return $this;
    }
    
<?php elseif ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
    public function <?= $relationship->getPropertyName(); ?>() {
        return $this->belongsTo(\<?= $codegen->getNamespace(); ?>\Models\<?= $relationship->getTo()->getClassName(); ?>::class);
    }
    
    public function get<?= $relationship->getClassName(); ?>() {
        if ($this-><?= $relationship->getPropertyName(); ?> === null) {
            $this-><?= $relationship->getPropertyName(); ?> = $this-><?= $relationship->getPropertyName(); ?>()->first();
        }
        return $this-><?= $relationship->getPropertyName(); ?>;
    }
    
    public function set<?= $relationship->getClassName(); ?>(\<?= $codegen->getNamespace(); ?>\Models\<?= $relationship->getTo()->getClassName(); ?> $<?= $relationship->getPropertyName(); ?> = null) {
        $this-><?= $relationship->getPropertyName(); ?> = $<?= $relationship->getPropertyName(); ?>;
        return $this;
    }
    
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>

    // Attribute accessors
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\StringAttribute
    || $attribute instanceof \Rhino\Codegen\Attribute\TextAttribute): ?>
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }
    
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\IntAttribute): ?>
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }
    
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DecimalAttribute): ?>
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }
    
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }
    
<?php endif; ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
    public function is<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getColumnName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getColumnName(); ?> = $value;
        return $this;
    }
    
<?php endif; ?>
<?php endforeach; ?>
    // Created/updated date accessors
    public function getCreated() {
        return $this->created_at;
    }
    
    public function setCreated(\DateTimeImmutable $created) {
        $this->created_at = $created;
        return $this;
    }

    public function getUpdated() {
        return $this->updated_at;
    }
    
    public function setUpdated(\DateTimeImmutable $updated) {
        $this->updated_at = $updated;
        return $this;
    }
    
}
