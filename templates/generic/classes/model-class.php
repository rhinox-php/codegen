<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->class; ?> {

<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('array')): ?>
<?php if ($attribute->nullable): ?>
    /** @var array|null <?= $attribute->getPluralName(); ?> */
    protected $<?= $attribute->pluralProperty; ?>;
<?php else: ?>
    /** @var array <?= $attribute->getPluralName(); ?> */
    protected $<?= $attribute->pluralProperty; ?> = [];
<?php endif; ?>
<?php elseif ($attribute->is('string', 'text')): ?>
    /** @var string|null <?= $attribute->name; ?> */
    protected $<?= $attribute->property; ?>;

<?php elseif ($attribute->is('int')): ?>
    /** @var int|null <?= $attribute->name; ?> */
    protected $<?= $attribute->property; ?>;

<?php elseif ($attribute->is('decimal')): ?>
    /** @var float|null <?= $attribute->name; ?> */
    protected $<?= $attribute->property; ?>;

<?php elseif ($attribute->is('bool')): ?>
    /** @var bool|null <?= $attribute->name; ?> */
    protected $<?= $attribute->property; ?>;

<?php else: ?>
    /** @var mixed <?= $attribute->name; ?> */
    protected $<?= $attribute->property; ?>;

<?php endif; ?>
<?php endforeach; ?>

<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text')): ?>
    public function get<?= $attribute->method; ?>(): <?= $attribute->nullable ? '?' : ''; ?>string {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>(string $value): self {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is('int')): ?>
    public function get<?= $attribute->method; ?>(): <?= $attribute->nullable ? '?' : ''; ?>int {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>($value): self {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is('decimal')): ?>
    public function get<?= $attribute->method; ?>(): <?= $attribute->nullable ? '?' : ''; ?>float {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>($value): self {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is('bool')): ?>

    public function is<?= $attribute->method; ?>(): <?= $attribute->nullable ? '?' : ''; ?>bool {
<?php if ($attribute->nullable): ?>
        if ($this-><?= $attribute->property; ?> === null) {
            return null;
        }
<?php endif; ?>
        return $this-><?= $attribute->property; ?> ? true : false;
    }

    public function set<?= $attribute->method; ?>(bool $value): self {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is('date', 'datettime')): ?>

    public function get<?= $attribute->method; ?>(): <?= $attribute->nullable ? '?' : ''; ?>\DateTimeInterface {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>(\DateTimeInterface $value = null): self {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is('object')): ?>

    public function get<?= $attribute->method; ?>(): <?= $attribute->nullable ? '?' : ''; ?>\<?= $attribute->getClass(); ?> {
        return $this-><?= $attribute->property; ?>;
    }

    public function set<?= $attribute->method; ?>(\<?= $attribute->getClass(); ?> $value): self {
        $this-><?= $attribute->property; ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is('array')): ?>

    public function get<?= $attribute->pluralMethod; ?>(): <?= $attribute->nullable ? '?' : ''; ?>array {
        return $this-><?= $attribute->pluralProperty; ?>;
    }

    public function set<?= $attribute->pluralMethod; ?>(array $<?= $attribute->pluralProperty; ?>): self {
        $this-><?= $attribute->pluralProperty; ?> = $<?= $attribute->pluralProperty; ?>;
        return $this;
    }

    public function add<?= $attribute->method; ?>($<?= $attribute->property; ?>): self {
        $this-><?= $attribute->pluralProperty; ?>[] = $<?= $attribute->property; ?>;
        return $this;
    }

<?php endif; ?>
<?php endforeach; ?>
}
