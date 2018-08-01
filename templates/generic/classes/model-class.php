<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->getClassName(); ?> {

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isType(['Array'])): ?>
<?php if ($attribute->isNullable()): ?>
    /** @var array|null <?= $attribute->getPluralName(); ?> */
    protected $<?= $attribute->getPluralPropertyName(); ?>;
<?php else: ?>
    /** @var array <?= $attribute->getPluralName(); ?> */
    protected $<?= $attribute->getPluralPropertyName(); ?> = [];
<?php endif; ?>
<?php elseif ($attribute->isType(['String', 'Text'])): ?>
    /** @var string|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php elseif ($attribute->isType(['Int'])): ?>
    /** @var int|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php elseif ($attribute->isType(['Decimal'])): ?>
    /** @var float|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php elseif ($attribute->isType(['Bool'])): ?>
    /** @var bool|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php else: ?>
    /** @var mixed <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php endif; ?>
<?php endforeach; ?>

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isType(['String', 'Text'])): ?>
    public function get<?= $attribute->getMethodName(); ?>(): <?= $attribute->isNullable() ? '?' : ''; ?>string {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(string $value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->isType(['Int'])): ?>
    public function get<?= $attribute->getMethodName(); ?>(): <?= $attribute->isNullable() ? '?' : ''; ?>int {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->isType(['Decimal'])): ?>
    public function get<?= $attribute->getMethodName(); ?>(): <?= $attribute->isNullable() ? '?' : ''; ?>float {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->isType(['Bool'])): ?>

    public function is<?= $attribute->getMethodName(); ?>(): <?= $attribute->isNullable() ? '?' : ''; ?>bool {
<?php if ($attribute->isNullable()): ?>
        if ($this-><?= $attribute->getPropertyName(); ?> === null) {
            return null;
        }
<?php endif; ?>
        return $this-><?= $attribute->getPropertyName(); ?> ? true : false;
    }

    public function set<?= $attribute->getMethodName(); ?>(bool $value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->isType(['Date', 'DateTime'])): ?>

    public function get<?= $attribute->getMethodName(); ?>(): <?= $attribute->isNullable() ? '?' : ''; ?>\DateTimeInterface {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value = null): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->isType(['Object'])): ?>

    public function get<?= $attribute->getMethodName(); ?>(): <?= $attribute->isNullable() ? '?' : ''; ?>\<?= $attribute->getClass(); ?> {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\<?= $attribute->getClass(); ?> $value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->isType(['Array'])): ?>

    public function get<?= $attribute->getPluralMethodName(); ?>(): <?= $attribute->isNullable() ? '?' : ''; ?>array {
        return $this-><?= $attribute->getPluralPropertyName(); ?>;
    }

    public function set<?= $attribute->getPluralMethodName(); ?>(array $<?= $attribute->getPluralPropertyName(); ?>): self {
        $this-><?= $attribute->getPluralPropertyName(); ?> = $<?= $attribute->getPluralPropertyName(); ?>;
        return $this;
    }

    public function add<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>): self {
        $this-><?= $attribute->getPluralPropertyName(); ?>[] = $<?= $attribute->getPropertyName(); ?>;
        return $this;
    }

<?php endif; ?>
<?php endforeach; ?>
}
