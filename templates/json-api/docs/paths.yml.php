/ping:
  get:
    summary: Ping the API
    responses:
      '200':
        description: Pong
<?php foreach ($entities as $entity): ?>
/<?= $entity->getRouteName(); ?>/index:
  get:
    summary: Get a list of <?= $entity->getLabel(); ?>

    responses:
      '200':
        description: List of <?= $entity->getLabel(); ?>

        schema:
          type: object
          properties:
            data:
              type: array
              items:
                $ref: "#/definitions/<?= $entity->getClassName(); ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->getRelationships() as $relationship): ?>
                  - $ref: '#/definitions/<?= $relationship->getClassName(); ?>Relationship'
<?php endforeach; ?>

/<?= $entity->getRouteName(); ?>/get/{id}:
  get:
    summary: Get a <?= $entity->getLabel(); ?> by ID
    responses:
      '200':
        description: Single of <?= $entity->getLabel(); ?>

        schema:
          type: object
          properties:
            data:
              $ref: "#/definitions/<?= $entity->getClassName(); ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
                  - $ref: '#/definitions/<?= $relationship->getClassName(); ?>Relationship'
<?php endif; ?>
<?php endforeach; ?>

/<?= $entity->getRouteName(); ?>/create:
  post:
    summary: Create a <?= $entity->getLabel(); ?>

    responses:
      '200':
        description: New <?= $entity->getLabel(); ?>

        schema:
          type: object
          properties:
            data:
              $ref: "#/definitions/<?= $entity->getClassName(); ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
                  - $ref: '#/definitions/<?= $relationship->getClassName(); ?>Relationship'
<?php endif; ?>
<?php endforeach; ?>

/<?= $entity->getRouteName(); ?>/update/{id}:
  post:
    summary: Update a <?= $entity->getLabel(); ?>

    responses:
      '200':
        description: Updated <?= $entity->getLabel(); ?>

        schema:
          type: object
          properties:
            data:
              $ref: "#/definitions/<?= $entity->getClassName(); ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
                  - $ref: '#/definitions/<?= $relationship->getClassName(); ?>Relationship'
<?php endif; ?>
<?php endforeach; ?>

/<?= $entity->getRouteName(); ?>/delete/{id}:
  post:
    summary: Delete a <?= $entity->getLabel(); ?>

    responses:
      '200':
        description: Deleted <?= $entity->getLabel(); ?>

        schema:
          type: object
          properties:
            data:
              $ref: "#/definitions/<?= $entity->getClassName(); ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->getRelationships() as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
                  - $ref: '#/definitions/<?= $relationship->getClassName(); ?>Relationship'
<?php endif; ?>
<?php endforeach; ?>

<?php endforeach; ?>
