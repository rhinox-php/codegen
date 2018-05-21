<?= '<?php'; ?>

namespace <?= $this->getNamespace('test-model'); ?>;
use <?= $this->getNamespace('model-generated'); ?>\<?= $entity->getClassName(); ?>;

class <?= $entity->getClassName(); ?>Test extends <?= $this->getTestBaseClass(); ?> {
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

    public function testJsonSerialize(): void {
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $json = json_encode($<?= $entity->getPropertyName(); ?>);
        $object = json_decode($json);
        $this->assertTrue(json_last_error() === JSON_ERROR_NONE);
    }

<?php foreach ($entity->getAttributes() as $attribute): ?>
    public function testGetSet<?= $attribute->getMethodName(); ?>() {
        // Create
        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $this->assertNull($<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());

        $<?= $attribute->getPropertyName(); ?> = <?= $this->generateTestAttribute($attribute); ?>;

        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertSame($<?= $attribute->getPropertyName(); ?>, $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>());
        $<?= $entity->getPropertyName(); ?>->save();

        // Update
        $<?= $attribute->getPropertyName(); ?> = <?= $this->generateTestAttribute($attribute); ?>;
        $<?= $entity->getPropertyName(); ?>->set<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $<?= $entity->getPropertyName(); ?>->save();

        $id = $<?= $entity->getPropertyName(); ?>->getId();
        $this->assertNotNull($id);

        $fetched = <?= $entity->getClassName(); ?>::findFirstBy<?= $attribute->getMethodName(); ?>($<?= $attribute->getPropertyName(); ?>);
        $this->assertNotNull($fetched, 'Could not find <?= $entity->getClassName(); ?> with <?= $attribute->getLabel(); ?> equal to ' . $<?= $attribute->getPropertyName(); ?>);
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
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>

    public function test<?= $relationship->getMethodName(); ?>Relationship(): void {
        $<?= $relationship->getTo()->getPluralPropertyName(); ?> = [
            new \<?= $this->getNamespace('model-generated'); ?>\<?= $relationship->getTo()->getClassName(); ?>(),
            new \<?= $this->getNamespace('model-generated'); ?>\<?= $relationship->getTo()->getClassName(); ?>(),
        ];

        $<?= $entity->getPropertyName(); ?> = new <?= $entity->getClassName(); ?>();
        $<?= $entity->getPropertyName(); ?>->set<?= $relationship->getPluralMethodName(); ?>($<?= $relationship->getTo()->getPluralPropertyName(); ?>);
        $<?= $entity->getPropertyName(); ?>->save();

        $id = $<?= $entity->getPropertyName(); ?>->getId();
        $<?= $entity->getPropertyName(); ?> = <?= $entity->getClassName(); ?>::findById($id);

        $count = 0;
        foreach ($<?= $entity->getPropertyName(); ?>->get<?= $relationship->getPluralMethodName(); ?>() as $related) {
            $count++;
        }
        $this->assertSame(count($<?= $relationship->getTo()->getPluralPropertyName(); ?>), $count);

        unset($<?= $relationship->getTo()->getPluralPropertyName(); ?>[1]);
        $<?= $entity->getPropertyName(); ?>->set<?= $relationship->getPluralMethodName(); ?>($<?= $relationship->getTo()->getPluralPropertyName(); ?>);
        $<?= $entity->getPropertyName(); ?>->save();

        $count = 0;
        foreach ($<?= $entity->getPropertyName(); ?>->get<?= $relationship->getPluralMethodName(); ?>() as $related) {
            $count++;
        }
        $this->assertSame(count($<?= $relationship->getTo()->getPluralPropertyName(); ?>), $count);

        unset($<?= $relationship->getTo()->getPluralPropertyName(); ?>[1]);
        $<?= $entity->getPropertyName(); ?>->set<?= $relationship->getPluralMethodName(); ?>([]);
        $<?= $entity->getPropertyName(); ?>->save();

        $count = 0;
        foreach ($<?= $entity->getPropertyName(); ?>->get<?= $relationship->getPluralMethodName(); ?>() as $related) {
            $count++;
        }
        $this->assertSame(0, $count);
    }

<?php endforeach; ?>

}
