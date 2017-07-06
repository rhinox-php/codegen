<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-generated'); ?>;

class <?= $entity->getClassName(); ?> {

    // Properties
<?php foreach ($entity->getAttributes() as $attribute): ?>
    protected $<?= $attribute->getPropertyName(); ?>;
<?php endforeach; ?>

    // Attribute accessors
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String', 'Text', 'Int', 'Decimal'])): ?>

    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>($value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php elseif ($attribute->is(['Bool'])): ?>

    public function is<?= $attribute->getMethodName(); ?>() {
<?php if ($attribute->isNullable()): ?>
        if ($this-><?= $attribute->getPropertyName(); ?> === null) {
            return null;
        }
<?php endif; ?>
        return $this-><?= $attribute->getPropertyName(); ?> ? true : false;
    }

    public function set<?= $attribute->getMethodName(); ?>(bool $value) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php elseif ($attribute->is(['Date', 'DateTime'])): ?>

    public function get<?= $attribute->getMethodName(); ?>() {
        return $this-><?= $attribute->getPropertyName(); ?>;
    }

    public function set<?= $attribute->getMethodName(); ?>(\DateTimeInterface $value = null) {
        $this-><?= $attribute->getPropertyName(); ?> = $value;
        return $this;
    }
<?php endif; ?>
<?php endforeach; ?>

}
