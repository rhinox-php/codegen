<?= '<?php'; ?>

namespace <?= $this->getNamespace('model-audit'); ?>;

class <?= $entity->class; ?> {

    public $snapshot;
    public $diff;
    public $mapper;

    public function __construct($mapper) {
        $this->mapper = $mapper;
    }

    public function snapshot(\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> $<?= $entity->property; ?>) {
        $this->snapshot = static::getSnapshot($<?= $entity->property; ?>, $this->mapper);
    }

    public function diff(\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> $with) {
        $withSnapshot = static::getSnapshot($with, $this->mapper);
        $this->diff = static::getDiff($this->snapshot, $withSnapshot, $this->mapper);
        return $this;
    }

    public static function getSnapshot(\<?= $this->getNamespace('model-implemented'); ?>\<?= $entity->class; ?> $<?= $entity->property; ?>, $mapper) {
        $snapshot = [
            'id' => $<?= $entity->property; ?>->getId(),
            'entity' => [],
            'relationships' => [],
        ];
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('string', 'text', 'int', 'decimal')): ?>
        $snapshot['entity']['<?= $attribute->property; ?>'] = $<?= $entity->property; ?>->get<?= $attribute->method; ?>();
<?php elseif ($attribute->is('bool')): ?>
        $snapshot['entity']['<?= $attribute->property; ?>'] = $<?= $entity->property; ?>->is<?= $attribute->method; ?>();
<?php elseif ($attribute->is('date')): ?>
        $snapshot['entity']['<?= $attribute->property; ?>'] = $<?= $entity->property; ?>->get<?= $attribute->method; ?>();
        if ($snapshot['entity']['<?= $attribute->property; ?>']) {
            $snapshot['entity']['<?= $attribute->property; ?>'] = $snapshot['entity']['<?= $attribute->property; ?>']->format('Y-m-d');
        }
<?php elseif ($attribute->is('date-time')): ?>
        $snapshot['entity']['<?= $attribute->property; ?>'] = $<?= $entity->property; ?>->get<?= $attribute->method; ?>();
        if ($snapshot['entity']['<?= $attribute->property; ?>']) {
            $snapshot['entity']['<?= $attribute->property; ?>'] = $snapshot['entity']['<?= $attribute->property; ?>']->format(DATE_ISO8601);
        }
<?php endif; ?>
<?php endforeach; ?>

<?php foreach ($entity->children('has-many', 'has-one') as $relationship): ?>
<?php if ($relationship->is('has-many')): ?>
        $snapshot['relationships']['<?= $relationship->pluralProperty; ?>'] = [];
        foreach ($<?= $entity->property; ?>->get<?= $relationship->pluralMethod; ?>() as $i => $<?= $relationship->property; ?>) {
            $relationshipSnapshot = <?= $relationship->class; ?>::getSnapshot($<?= $relationship->property; ?>, $mapper);
            $index = $mapper->mapRelationshipKey($relationshipSnapshot, $i, '<?= $relationship->class; ?>');
            $snapshot['relationships']['<?= $relationship->pluralProperty; ?>'][$index] = $relationshipSnapshot;
        }

<?php endif; ?>
<?php if ($relationship->is('has-one')): ?>
        $<?= $relationship->property; ?> = $<?= $entity->property; ?>->get<?= $relationship->method; ?>();
        if ($<?= $relationship->property; ?>) {
            $relationshipSnapshot = <?= $relationship->class; ?>::getSnapshot($<?= $relationship->property; ?>, $mapper);
            $snapshot['relationships']['<?= $relationship->property; ?>'] = $relationshipSnapshot;
        }

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

<?php foreach ($entity->children('has-many', 'has-one') as $relationship): ?>
<?php if ($relationship->is('has-many')): ?>
        $diff['relationships']['<?= $relationship->pluralProperty; ?>'] = [];

        // Check existing and removed <?= $relationship->name; ?>

        foreach ($entityA['relationships']['<?= $relationship->pluralProperty; ?>'] as $i => $<?= $relationship->property; ?>) {
            $index = $mapper->mapRelationshipKey($<?= $relationship->property; ?>, $i, '<?= $relationship->class; ?>');
            $relationshipDiff = <?= $relationship->class; ?>::getDiff($<?= $relationship->property; ?>, $entityB['relationships']['<?= $relationship->pluralProperty; ?>'][$index] ?? [], $mapper);
            $diff['relationships']['<?= $relationship->pluralProperty; ?>'][$index] = $relationshipDiff;
        }

        // Check for new <?= $relationship->name; ?>

        foreach ($entityB['relationships']['<?= $relationship->pluralProperty; ?>'] as $i => $<?= $relationship->property; ?>) {
            $index = $mapper->mapRelationshipKey($<?= $relationship->property; ?>, $i, '<?= $relationship->class; ?>');
            if (!isset($diff['relationships']['<?= $relationship->pluralProperty; ?>'][$index])) {
                $relationshipDiff = <?= $relationship->class; ?>::getDiff([], $<?= $relationship->property; ?>, $mapper);
                $diff['relationships']['<?= $relationship->pluralProperty; ?>'][$index] = $relationshipDiff;
            }
        }

        $diff['relationships']['<?= $relationship->pluralProperty; ?>'] = array_filter($diff['relationships']['<?= $relationship->pluralProperty; ?>'], function($relationshipDiff) {
            return !empty($relationshipDiff['entity']) || !empty($relationshipDiff['relationships']);
        });
        if (count($diff['relationships']['<?= $relationship->pluralProperty; ?>']) === 0) {
            unset($diff['relationships']['<?= $relationship->pluralProperty; ?>']);
        }

<?php endif; ?>
<?php if ($relationship->is('has-one')): ?>
        $<?= $relationship->property; ?> = $entityA['relationships']['<?= $relationship->property; ?>'] ?? null;
        if ($<?= $relationship->property; ?>) {
            $relationshipDiff = <?= $relationship->class; ?>::getDiff($<?= $relationship->property; ?>, $entityB['relationships']['<?= $relationship->property; ?>'], $mapper);
            if (!empty($relationshipDiff['entity']) || !empty($relationshipDiff['relationships'])) {
                $diff['relationships']['<?= $relationship->property; ?>'] = $relationshipDiff;
            }
        }

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
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('date-time')): ?>
            if (isset($diff['entity']['<?= $attribute->property; ?>'][0])) {
                $diff['entity']['<?= $attribute->property; ?>'][0] = (new \DateTime($diff['entity']['<?= $attribute->property; ?>'][0]))->setTimezone(tz())->format(DATE_ISO8601);
            }
            if (isset($diff['entity']['<?= $attribute->property; ?>'][1])) {
                $diff['entity']['<?= $attribute->property; ?>'][1] = (new \DateTime($diff['entity']['<?= $attribute->property; ?>'][1]))->setTimezone(tz())->format(DATE_ISO8601);
            }
            if (!$mapper->filterAttribute('<?= $entity->class; ?>', '<?= $attribute->property; ?>')) {
                $result .= static::diffAttribute($diff['entity'], '<?= $attribute->property; ?>', '<?= $attribute->label; ?>', $mapper, '<?= $entity->class; ?>');
            }
<?php else: ?>
            if (!$mapper->filterAttribute('<?= $entity->class; ?>', '<?= $attribute->property; ?>')) {
                $result .= static::diffAttribute($diff['entity'], '<?= $attribute->property; ?>', '<?= $attribute->label; ?>', $mapper, '<?= $entity->class; ?>');
            }
<?php endif; ?>
<?php endforeach; ?>
        }

        if (trim($result)) {
            $result = '<?= $entity->name; ?> ' . $index . PHP_EOL . $result;
        }

<?php foreach ($entity->children('has-many', 'has-one') as $relationship): ?>
<?php if ($relationship->is('has-many')): ?>
        foreach ($diff['relationships']['<?= $relationship->pluralProperty; ?>'] ?? [] as $i => $<?= $relationship->property; ?>) {
            $result .= <?= $relationship->class; ?>::diffToString($<?= $relationship->property; ?>, $mapper, $i);
        }
<?php endif; ?>
<?php if ($relationship->is('has-one')): ?>
        if (isset($diff['relationships']['<?= $relationship->property; ?>'])) {
            $result .= <?= $relationship->class; ?>::diffToString($diff['relationships']['<?= $relationship->property; ?>'], $mapper);
        }
<?php endif; ?>
<?php endforeach; ?>
        return $result;
    }
}
