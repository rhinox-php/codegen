<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->getClassName(); ?> {

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['Array'])): ?>
<?php if ($attribute->isNullable()): ?>
    /** @var array|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPluralPropertyName(); ?>;
<?php else: ?>
    /** @var array <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPluralPropertyName(); ?> = [];
<?php endif; ?>
<?php elseif ($attribute->is(['String', 'Text'])): ?>
    /** @var string|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php elseif ($attribute->is(['Int'])): ?>
    /** @var int|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php elseif ($attribute->is(['Decimal'])): ?>
    /** @var float|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php elseif ($attribute->is(['Bool'])): ?>
    /** @var bool|null <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;

<?php else: ?>
    /** @var mixed <?= $attribute->getName(); ?> */
    protected $<?= $attribute->getPropertyName(); ?>;
    
<?php endif; ?>
<?php endforeach; ?>

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String', 'Text'])): ?>
    public function get<?= $attribute->getMethodName(); ?>(): string {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is(['Int'])): ?>
    public function get<?= $attribute->getMethodName(); ?>(): int {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is(['Decimal'])): ?>
    public function get<?= $attribute->getMethodName(); ?>(): float {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is(['Bool'])): ?>

    public function is<?= $attribute->getMethodName(); ?>(): bool {
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

<?php elseif ($attribute->is(['Date', 'DateTime'])): ?>

    public function get<?= $attribute->getMethodName(); ?>(): \DateTimeInterface {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value = null): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is(['Object'])): ?>

    public function get<?= $attribute->getMethodName(); ?>(): \<?= $attribute->getClass(); ?> {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\<?= $attribute->getClass(); ?> $value): self {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }

<?php elseif ($attribute->is(['Array'])): ?>

    public function get<?= $attribute->getPluralMethodName(); ?>(): array {
        return $this-><?= $attribute->getPluralPropertyName(); ?>;
    }

    public function set<?= $attribute->getPluralMethodName(); ?>(array $<?= $attribute->getPluralPropertyName(); ?>): self {
        $this-><?= $attribute->getPluralPropertyName(); ?> = $<?= $attribute->getPluralPropertyName(); ?>;
        return $this;
    }

    public function add<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>): self {
        $this-><?= $attribute->getPropertyName(); ?> = $<?= $attribute->getPropertyName(); ?>;
        return $this;
    }

<?php endif; ?>
<?php endforeach; ?>
}
