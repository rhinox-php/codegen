<?= '<?php'; ?>

namespace <?= $this->getNamespace('test-model'); ?>;
use <?= $this->getNamespace('model-generated'); ?>\<?= $entity->class; ?>;

class <?= $entity->class; ?>Test extends <?= $this->getTestBaseClass(); ?> {
    public function testConstructor(): void {
        $<?= $entity->property; ?> = new <?= $entity->class; ?>();
        $this->assertInstanceOf(<?= $entity->class; ?>::class, $<?= $entity->property; ?>);
    }

    public function testCreateAndFetch(): void {
        $<?= $entity->property; ?> = new <?= $entity->class; ?>();
        $<?= $entity->property; ?>->save();

        $id = $<?= $entity->property; ?>->getId();
        $this->assertNotNull($id);
        $fetched = <?= $entity->class; ?>::findById($id);
        $this->assertInstanceOf(<?= $entity->class; ?>::class, $fetched);
        $fetched->delete();

        $deleted = <?= $entity->class; ?>::findById($id);
        $this->assertNull($deleted);
    }

    public function testIterateAndCount(): void {
        $count = 0;
        foreach (<?= $entity->class; ?>::getAll() as $instance) {
            $this->assertInstanceOf(<?= $entity->class; ?>::class, $instance);
            $count++;
        }
        $this->assertSame($count, <?= $entity->class; ?>::countAll());
    }

    public function testJsonSerialize(): void {
        $<?= $entity->property; ?> = new <?= $entity->class; ?>();
        $json = json_encode($<?= $entity->property; ?>);
        $object = json_decode($json);
        $this->assertTrue(json_last_error() === JSON_ERROR_NONE);
    }

<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
    public function testGetSet<?= $attribute->method; ?>() {
        // Create
        $<?= $entity->property; ?> = new <?= $entity->class; ?>();
        $this->assertNull($<?= $entity->property; ?>->get<?= $attribute->method; ?>());

        $<?= $attribute->property; ?> = <?= $this->generateTestAttribute($attribute); ?>;

        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($<?= $attribute->property; ?>);
        $this->assertSame($<?= $attribute->property; ?>, $<?= $entity->property; ?>->get<?= $attribute->method; ?>());
        $<?= $entity->property; ?>->save();

        // Update
        $<?= $attribute->property; ?> = <?= $this->generateTestAttribute($attribute); ?>;
        $<?= $entity->property; ?>->set<?= $attribute->method; ?>($<?= $attribute->property; ?>);
        $<?= $entity->property; ?>->save();

        $id = $<?= $entity->property; ?>->getId();
        $this->assertNotNull($id);

        $fetched = <?= $entity->class; ?>::findFirstBy<?= $attribute->method; ?>($<?= $attribute->property; ?>);
        $this->assertNotNull($fetched, 'Could not find <?= $entity->class; ?> with <?= $attribute->label; ?> equal to ' . $<?= $attribute->property; ?>);
        $this->assertInstanceOf(<?= $entity->class; ?>::class, $fetched);
        $this->assertSame($id, $fetched->getId());
        $this->assertSame($<?= $attribute->property; ?>, $fetched->get<?= $attribute->method; ?>());

        $count = 0;
        foreach (<?= $entity->class; ?>::findBy<?= $attribute->method; ?>($<?= $attribute->property; ?>) as $instance) {
            $this->assertInstanceOf(<?= $entity->class; ?>::class, $instance);
            $count++;
        }
        $this->assertSame($count, <?= $entity->class; ?>::countBy<?= $attribute->method; ?>($<?= $attribute->property; ?>));
    }

<?php endforeach; ?>
<?php foreach ($entity->iterateRelationshipsByType(['BelongsTo']) as $relationship): ?>

    public function test<?= $relationship->method; ?>Relationship(): void {
        $<?= $relationship->getTo()->property; ?> = new \<?= $this->getNamespace('model-generated'); ?>\<?= $relationship->getTo()->class; ?>();
        $<?= $relationship->getTo()->property; ?>->save();

        $<?= $entity->property; ?> = new <?= $entity->class; ?>();
        $this->assertFalse($<?= $entity->property; ?>->has<?= $relationship->method; ?>());
        $<?= $entity->property; ?>->set<?= $relationship->method; ?>Id($<?= $relationship->getTo()->property; ?>->getId());
        $<?= $entity->property; ?>->save();

        $found = <?= $entity->class; ?>::findFirstBy<?= $relationship->method; ?>Id($<?= $relationship->getTo()->property; ?>->getId());
        $this->assertNotNull($found);
        $this->assertSame($<?= $entity->property; ?>->getId(), $found->getId());

        $count = 0;
        foreach (<?= $entity->class; ?>::findBy<?= $relationship->method; ?>Id($<?= $relationship->getTo()->property; ?>->getId()) as $instance) {
            $count++;
        }
        $this->assertSame($count, 1);
        $this->assertSame($count, <?= $entity->class; ?>::countBy<?= $relationship->method; ?>Id($<?= $relationship->getTo()->property; ?>->getId()));

        $this->assertInstanceOf(\<?= $this->getNamespace('model-generated'); ?>\<?= $relationship->getTo()->class; ?>::class, $<?= $entity->property; ?>->get<?= $relationship->method; ?>());
        $this->assertTrue($<?= $entity->property; ?>->has<?= $relationship->method; ?>());

        $<?= $entity->property; ?>->set<?= $relationship->method; ?>Id(null);
        $<?= $entity->property; ?>->save();
        $this->assertFalse($<?= $entity->property; ?>->has<?= $relationship->method; ?>());
    }

<?php endforeach; ?>
<?php foreach ($entity->iterateRelationshipsByType(['HasMany']) as $relationship): ?>

    public function test<?= $relationship->method; ?>Relationship(): void {
        $<?= $relationship->getTo()->pluralProperty; ?> = [
            new \<?= $this->getNamespace('model-generated'); ?>\<?= $relationship->getTo()->class; ?>(),
            new \<?= $this->getNamespace('model-generated'); ?>\<?= $relationship->getTo()->class; ?>(),
        ];

        $<?= $entity->property; ?> = new <?= $entity->class; ?>();
        $<?= $entity->property; ?>->set<?= $relationship->pluralMethod; ?>($<?= $relationship->getTo()->pluralProperty; ?>);
        $<?= $entity->property; ?>->save();

        $id = $<?= $entity->property; ?>->getId();
        $<?= $entity->property; ?> = <?= $entity->class; ?>::findById($id);

        $count = 0;
        foreach ($<?= $entity->property; ?>->get<?= $relationship->pluralMethod; ?>() as $related) {
            $count++;
        }
        $this->assertSame(count($<?= $relationship->getTo()->pluralProperty; ?>), $count);

        unset($<?= $relationship->getTo()->pluralProperty; ?>[1]);
        $<?= $entity->property; ?>->set<?= $relationship->pluralMethod; ?>($<?= $relationship->getTo()->pluralProperty; ?>);
        $<?= $entity->property; ?>->save();

        $count = 0;
        foreach ($<?= $entity->property; ?>->get<?= $relationship->pluralMethod; ?>() as $related) {
            $count++;
        }
        $this->assertSame(count($<?= $relationship->getTo()->pluralProperty; ?>), $count);

        unset($<?= $relationship->getTo()->pluralProperty; ?>[1]);
        $<?= $entity->property; ?>->set<?= $relationship->pluralMethod; ?>([]);
        $<?= $entity->property; ?>->save();

        $count = 0;
        foreach ($<?= $entity->property; ?>->get<?= $relationship->pluralMethod; ?>() as $related) {
            $count++;
        }
        $this->assertSame(0, $count);
    }

<?php endforeach; ?>

}
