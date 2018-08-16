type: object
properties:
  id:
    type: string
    description: Not used when creating.
  type:
    type: string
    description: Must be <?= $entity->class; ?>.
    enum:
      - <?= $entity->class; ?>

  attributes:
    type: object
    properties:
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
      <?= $attribute->property; ?>:
        type: <?= $attribute->getType(); ?>

<?php endforeach; ?>
  relationships:
    type: object
    properties:
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
      <?= $relationship->property; ?>:
        type: object
        properties:
          data:
            $ref: '#/definitions/<?= $relationship->getTo()->class; ?>Relationship'
<?php endforeach; ?>
