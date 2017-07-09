<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->getClassName(); ?> {

<?php foreach ($entity->getAttributes() as $attribute): ?>
    protected $<?= $attribute->getPropertyName(); ?>;
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

<?php endif; ?>
<?php endforeach; ?>
}
