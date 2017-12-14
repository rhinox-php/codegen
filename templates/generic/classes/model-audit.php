<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-audit'); ?>;

class <?= $entity->getClassName(); ?> {

    public $snapshot;
    public $diff;
    public $mapper;

    public function __construct($mapper) {
        $this->mapper = $mapper;
    }

    public function snapshot(\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> $<?= $entity->getPropertyName(); ?>) {
        $this->snapshot = static::getSnapshot($<?= $entity->getPropertyName(); ?>, $this->mapper);
    }

    public function diff(\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> $with) {
        $withSnapshot = static::getSnapshot($with, $this->mapper);
        $this->diff = static::getDiff($this->snapshot, $withSnapshot, $this->mapper);
        return $this;
    }

    public static function getSnapshot(\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->getClassName(); ?> $<?= $entity->getPropertyName(); ?>, $mapper) {
        $snapshot = [
            'id' => $<?= $entity->getPropertyName(); ?>->getId(),
            'entity' => [],
            'relationships' => [],
        ];
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String', 'Text', 'Int', 'Decimal'])): ?>
        $snapshot['entity']['<?= $attribute->getPropertyName(); ?>'] = $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>();
<?php elseif ($attribute->is(['Bool'])): ?>
        $snapshot['entity']['<?= $attribute->getPropertyName(); ?>'] = $<?= $entity->getPropertyName(); ?>->is<?= $attribute->getMethodName(); ?>();
<?php elseif ($attribute->is(['Date'])): ?>
        $snapshot['entity']['<?= $attribute->getPropertyName(); ?>'] = $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>();
        if ($snapshot['entity']['<?= $attribute->getPropertyName(); ?>']) {
            $snapshot['entity']['<?= $attribute->getPropertyName(); ?>'] = $snapshot['entity']['<?= $attribute->getPropertyName(); ?>']->format('Y-m-d');
        }
<?php elseif ($attribute->is(['DateTime'])): ?>
        $snapshot['entity']['<?= $attribute->getPropertyName(); ?>'] = $<?= $entity->getPropertyName(); ?>->get<?= $attribute->getMethodName(); ?>();
        if ($snapshot['entity']['<?= $attribute->getPropertyName(); ?>']) {
            $snapshot['entity']['<?= $attribute->getPropertyName(); ?>'] = $snapshot['entity']['<?= $attribute->getPropertyName(); ?>']->format(DATE_ISO8601);
        }
<?php endif; ?>
<?php endforeach; ?>

<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
        $snapshot['relationships']['<?= $relationship->getPluralPropertyName(); ?>'] = [];
        foreach ($<?= $entity->getPropertyName(); ?>->get<?= $relationship->getPluralMethodName(); ?>() as $i => $<?= $relationship->getPropertyName(); ?>) {
            $relationshipSnapshot = <?= $relationship->getTo()->getClassName(); ?>::getSnapshot($<?= $relationship->getPropertyName(); ?>, $mapper);
            $index = $mapper->mapRelationshipKey($relationshipSnapshot, $i, '<?= $relationship->getTo()->getClassName(); ?>');
            $snapshot['relationships']['<?= $relationship->getPluralPropertyName(); ?>'][$index] = $relationshipSnapshot;
        }

<?php endif; ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
        $<?= $relationship->getPropertyName(); ?> = $<?= $entity->getPropertyName(); ?>->get<?= $relationship->getMethodName(); ?>();
        if ($<?= $relationship->getPropertyName(); ?>) {
            $relationshipSnapshot = <?= $relationship->getTo()->getClassName(); ?>::getSnapshot($<?= $relationship->getPropertyName(); ?>, $mapper);
            $snapshot['relationships']['<?= $relationship->getPropertyName(); ?>'] = $relationshipSnapshot;
        }

<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
        if (count($snapshot['relationships']) === 0) {
            unset($snapshot['relationships']);
        }

        return $snapshot;
    }

    public static function getDiff(array $entityA, array $entityB, $mapper) {
        $diff = [
            'id' => [$entityA['id'] ?? null, $entityB['id'] ?? null],
            'entity' => [],
            'relationships' => [],
        ];

        if (!isset($entityA['entity'])) {
            $entityA['entity'] = [];
        }
        if (!isset($entityA['relationships'])) {
            $entityA['relationships'] = [];
        }

        if (!isset($entityB['entity'])) {
            $entityB['entity'] = [];
        }
        if (!isset($entityB['relationships'])) {
            $entityB['relationships'] = [];
        }

        foreach ($entityA['entity'] as $attribute => $value) {
            $otherValue = $entityB['entity'][$attribute] ?? null;
            if ($value != $otherValue) {
                $diff['entity'][$attribute] = [$value, $otherValue];
            }
        }

        foreach ($entityB['entity'] as $attribute => $value) {
            $otherValue = $entityA['entity'][$attribute] ?? null;
            if ($value != $otherValue) {
                $diff['entity'][$attribute] = [$otherValue, $value];
            }
        }

        if (count($diff['entity']) === 0) {
            unset($diff['entity']);
        }

<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
        $diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>'] = [];

        // Check existing and removed <?= $relationship->getName(); ?>

        foreach ($entityA['relationships']['<?= $relationship->getPluralPropertyName(); ?>'] as $i => $<?= $relationship->getPropertyName(); ?>) {
            $index = $mapper->mapRelationshipKey($<?= $relationship->getPropertyName(); ?>, $i, '<?= $relationship->getTo()->getClassName(); ?>');
            $relationshipDiff = <?= $relationship->getTo()->getClassName(); ?>::getDiff($<?= $relationship->getPropertyName(); ?>, $entityB['relationships']['<?= $relationship->getPluralPropertyName(); ?>'][$index] ?? [], $mapper);
            $diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>'][$index] = $relationshipDiff;
        }

        // Check for new <?= $relationship->getName(); ?>

        foreach ($entityB['relationships']['<?= $relationship->getPluralPropertyName(); ?>'] as $i => $<?= $relationship->getPropertyName(); ?>) {
            $index = $mapper->mapRelationshipKey($<?= $relationship->getPropertyName(); ?>, $i, '<?= $relationship->getTo()->getClassName(); ?>');
            if (!isset($diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>'][$index])) {
                $relationshipDiff = <?= $relationship->getTo()->getClassName(); ?>::getDiff([], $<?= $relationship->getPropertyName(); ?>, $mapper);
                $diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>'][$index] = $relationshipDiff;
            }
        }

        $diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>'] = array_filter($diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>'], function($relationshipDiff) {
            return !empty($relationshipDiff['entity']) || !empty($relationshipDiff['relationships']);
        });
        if (count($diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>']) === 0) {
            unset($diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>']);
        }

<?php endif; ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
        $<?= $relationship->getPropertyName(); ?> = $entityA['relationships']['<?= $relationship->getPropertyName(); ?>'] ?? null;
        if ($<?= $relationship->getPropertyName(); ?>) {
            $relationshipDiff = <?= $relationship->getTo()->getClassName(); ?>::getDiff($<?= $relationship->getPropertyName(); ?>, $entityB['relationships']['<?= $relationship->getPropertyName(); ?>'], $mapper);
            if (!empty($relationshipDiff['entity']) || !empty($relationshipDiff['relationships'])) {
                $diff['relationships']['<?= $relationship->getPropertyName(); ?>'] = $relationshipDiff;
            }
        }

<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>

        if (count($diff['relationships']) === 0) {
            unset($diff['relationships']);
        }

        return $diff;
    }

    protected static function diffAttribute(array $diff, string $property, string $label, $mapper, string $type): string {
        if (isset($diff[$property])) {
            [$a, $b] = $diff[$property];
            $mapped = $mapper->mapDiffAttribute($type, $property, $a, $b);
            if ($mapped) {
                [$label, $a, $b] = $mapped;
            }
            if (is_bool($a)) {
                $a = $a ? 'true' : 'false';
            }
            if (is_bool($b)) {
                $b = $b ? 'true' : 'false';
            }
            if ($a === null && $b === null) {
                return '';
            } elseif ($a === null || $a === '') {
                return ' - Set ' . $label . ' to "' . trim($b) . '"' . PHP_EOL;
            } elseif ($b === null) {
                if ($a !== null && $a !== '') {
                    return ' - Unset ' . $label . ' from "' . trim($a) . '"' . PHP_EOL;
                }
            } else {
                return ' - Changed ' . $label . ' from "' . trim($a) . '" to "' . trim($b) . '"' . PHP_EOL;
            }
        }
        return '';
    }

    public static function diffToString(array $diff, $mapper, $index = null): string {
        $result = '';

        if ($index === null) {
            $index = $diff['id'][0] ?? null;
        } else {
            $index = (int) $index + 1;
        }

        if (isset($diff['entity'])) {
<?php foreach ($entity->getAttributes() as $attribute): ?>
            if (!$mapper->filterAttribute('<?= $entity->getClassName(); ?>', '<?= $attribute->getPropertyName(); ?>')) {
                $result .= static::diffAttribute($diff['entity'], '<?= $attribute->getPropertyName(); ?>', '<?= $attribute->getLabel(); ?>', $mapper, '<?= $entity->getClassName(); ?>');
            }
<?php endforeach; ?>
        }

        if (trim($result)) {
            $result = '<?= $entity->getName(); ?> ' . $index . PHP_EOL . $result;
        }

<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasMany): ?>
        foreach ($diff['relationships']['<?= $relationship->getPluralPropertyName(); ?>'] ?? [] as $i => $<?= $relationship->getPropertyName(); ?>) {
            $result .= <?= $relationship->getTo()->getClassName(); ?>::diffToString($<?= $relationship->getPropertyName(); ?>, $mapper, $i);
        }
<?php endif; ?>
<?php if ($relationship instanceof \Rhino\Codegen\Relationship\HasOne): ?>
        if (isset($diff['relationships']['<?= $relationship->getPropertyName(); ?>'])) {
            $result .= <?= $relationship->getTo()->getClassName(); ?>::diffToString($diff['relationships']['<?= $relationship->getPropertyName(); ?>'], $mapper);
        }
<?php endif; ?>
<?php endif; ?>
<?php endforeach; ?>
        return $result;
    }
}
