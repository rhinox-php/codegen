  <?= $entity->getClassName(); ?>:
    type: object
    properties:
      data:
        type: object
        properties:
          id:
            type: string
            description: Not used when creating.
          type:
            type: string
            description: Must be <?= $entity->getClassName(); ?>.
            enum: 
              - <?= $entity->getClassName(); ?>

          attributes:
            type: object
            properties:
<?php foreach ($entity->getAttributes() as $attribute): ?>
              <?= $attribute->getPropertyName(); ?>:
                type: <?= $attribute->getType(); ?>

<?php endforeach; ?>
          relationships:
            type: object
            properties:
<?php foreach ($entity->getRelationships() as $relationship): ?>
              <?= $relationship->getPropertyName(); ?>:
                type: object
                properties:
                  data:
                    $ref: '#/definitions/<?= $relationship->getTo()->getClassName(); ?>Relationship'
<?php endforeach; ?>
              printServices:
                type: object
                properties:
                  data:
                    $ref: '#/definitions/RegionPrintServiceRelationship'