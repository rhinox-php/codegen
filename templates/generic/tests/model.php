<?= '<?php'; ?>

namespace <?= $this->getNamespace('test-model'); ?>;
use <?= $this->getNamespace('model-generated'); ?>\<?= $entity->getClassName(); ?>;

class <?= $entity->getClassName(); ?>Test extends \PHPUnit\Framework\TestCase {
    public function testConstructor(): void {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertInstanceOf(<?= $entity->getClassName(); ?>::class, $<?= $entity->getPropertyName(); ?>);
    }

    public function testCreateAndFetch(): void {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $<?= $entity->getPropertyName(); ?>->save();

        $id = $<?= $entity->getPropertyName(); ?>->getId();
        $this->assertNotNull($id);
        $fetched = <?= $entity->getClassName(); ?>::findById($id);
        $this->assertInstanceOf(<?= $entity->getClassName(); ?>::class, $fetched);
        $fetched->delete();

        $deleted = <?= $entity->getClassName(); ?>::findById($id);
        $this->assertNull($deleted);
    }

    public function testIterateAndCount(): void {
        $count = 0;
        foreach (<?= $entity->getClassName(); ?>::getAll() as $instance) {
            $this->assertInstanceOf(<?= $entity->getClassName(); ?>::class, $instance);
            $count++;
        }
        $this->assertSame($count, <?= $entity->getClassName(); ?>::countAll());
    }

    public function testDataTable(): void {
        $dataTable = <?= $entity->getClassName(); ?>::getDataTable();

        $request = \Rhino\Http\Request::createDefault();
        $response = \Rhino\Http\Response::createDefault();
        $this->assertFalse($dataTable->process($request, $response));
    }

    public function testJsonSerialize(): void {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $json = json_encode($<?= $entity->getPropertyName(); ?>);
        $object = json_decode($json);
        $this->assertTrue(json_last_error() === JSON_ERROR_NONE);
    }

<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String'])): ?>

    public function testGetSet<?= $attribute->getMethodName(); ?>() {
        // Create
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertNull($<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $attribute->getPropertyName(); ?> = bin2hex(openssl_random_pseudo_bytes(127));
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $entity->getPropertyName(); ?>->save();

        // Update
        $<?= $attribute->getPropertyName(); ?> = bin2hex(openssl_random_pseudo_bytes(127));
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $<?= $entity->getPropertyName(); ?>->save();

        $id = $<?= $entity->getPropertyName(); ?>->getId();
        $this->assertNotNull($id);

        $fetched = <?= $entity->getClassName(); ?>::findFirstBy<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertInstanceOf(<?= $entity->getClassName(); ?>::class, $fetched);
        $this->assertSame($id, $fetched->getId());
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $fetched->get<?= $attribute->getMethodName(); ?>());

        $count = 0;
        foreach (<?= $entity->getClassName(); ?>::findBy<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>) as $instance) {
            $this->assertInstanceOf(<?= $entity->getClassName(); ?>::class, $instance);
            $count++;
        }
        $this->assertSame($count, <?= $entity->getClassName(); ?>::countBy<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>));
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

<?php foreach ($entity->iterateRelationshipsByType(['BelongsTo']) as $relationship): ?>

    public function test<?= $relationship->getMethodName(); ?>Relationship(): void {
        $<?= $relationship->getTo()->getPropertyName(); ?> = new \<?= $this->getNamespace('model-generated'); ?>\<?= $relationship->getTo()->getClassName(); ?>();
        $<?= $relationship->getTo()->getPropertyName(); ?>->save();

        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertFalse($<?= $entity->getPropertyName(); ?>->has<?= $relationship->getMethodName(); ?>());
        $<?= $entity->getPropertyName(); ?>->set<?= $relationship->getMethodName(); ?>Id($<?= $relationship->getTo()->getPropertyName(); ?>->getId());
        $<?= $entity->getPropertyName(); ?>->save();

        $found = <?= $entity->getClassName(); ?>::findFirstBy<?= $relationship->getMethodName(); ?>Id($<?= $relationship->getTo()->getPropertyName(); ?>->getId());
        $this->assertNotNull($found);
        $this->assertSame($<?= $entity->getPropertyName(); ?>->getId(), $found->getId());

        $count = 0;
        foreach (<?= $entity->getClassName(); ?>::findBy<?= $relationship->getMethodName(); ?>Id($<?= $relationship->getTo()->getPropertyName(); ?>->getId()) as $instance) {
            $count++;
        }
        $this->assertSame($count, 1);
        $this->assertSame($count, <?= $entity->getClassName(); ?>::countBy<?= $relationship->getMethodName(); ?>Id($<?= $relationship->getTo()->getPropertyName(); ?>->getId()));

        $this->assertInstanceOf(\<?= $this->getNamespace('model-generated'); ?>\<?= $relationship->getTo()->getClassName(); ?>::class, $<?= $entity->getPropertyName(); ?>->get<?= $relationship->getMethodName(); ?>());
        $this->assertTrue($<?= $entity->getPropertyName(); ?>->has<?= $relationship->getMethodName(); ?>());

        $<?= $entity->getPropertyName(); ?>->set<?= $relationship->getMethodName(); ?>Id(null);
        $<?= $entity->getPropertyName(); ?>->save();
        $this->assertFalse($<?= $entity->getPropertyName(); ?>->has<?= $relationship->getMethodName(); ?>());
    }

<?php endforeach; ?>
}
