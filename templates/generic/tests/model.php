<?= '<?php'; ?>

namespace <?= $this->getNamespace('test-model'); ?>;
use <?= $this->getNamespace('model-generated'); ?>\<?= $entity->getClassName(); ?>;

class <?= $entity->getClassName(); ?>Test extends \PHPUnit\Framework\TestCase {
    public function testConstructor(): void {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertInstanceOf(<?= $entity->getClassName(); ?>::class, $<?= $entity->getPropertyName(); ?>);
    }

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String'])): ?>

    public function testGetSet<?= $attribute->getMethodName(); ?>() {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertNull($<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $attribute->getPropertyName(); ?> = bin2hex(openssl_random_pseudo_bytes(128));
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
    }

<?php elseif ($attribute->is(['Text'])): ?>

    public function testGetSet<?= $attribute->getMethodName(); ?>() {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertNull($<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $attribute->getPropertyName(); ?> = bin2hex(openssl_random_pseudo_bytes(1024));
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
    }

<?php elseif ($attribute->is(['Int'])): ?>

    public function testGetSet<?= $attribute->getMethodName(); ?>() {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertNull($<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $attribute->getPropertyName(); ?> = mt_rand();
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
    }

<?php elseif ($attribute->is(['Decimal'])): ?>

    public function testGetSet<?= $attribute->getMethodName(); ?>() {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertNull($<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $attribute->getPropertyName(); ?> = mt_rand() / mt_getrandmax();
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
    }

<?php elseif ($attribute->is(['Bool'])): ?>

    public function testGetSet<?= $attribute->getMethodName(); ?>() {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertNull($<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $attribute->getPropertyName(); ?> = mt_rand() < 0.5;
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
    }

<?php elseif ($attribute->is(['Date', 'DateTime'])): ?>

    public function testGetSet<?= $attribute->getMethodName(); ?>() {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertNull($<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $attribute->getPropertyName(); ?> = new \DateTimeImmutable();
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
    }

<?php endif; ?>
<?php endforeach; ?>
}
