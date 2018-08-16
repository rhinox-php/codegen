/ping:
  get:
    summary: Ping the API
    responses:
      '200':
        description: Pong
<?php foreach ($entities as $entity): ?>
/<?= $entity->route; ?>/index:
  get:
    summary: Get a list of <?= $entity->label; ?>

    responses:
      '200':
        description: List of <?= $entity->label; ?>

        schema:
          type: object
          properties:
            data:
              type: array
              items:
                $ref: "#/definitions/<?= $entity->class; ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
                  - $ref: '#/definitions/<?= $relationship->class; ?>Relationship'
<?php endforeach; ?>

/<?= $entity->route; ?>/get/{id}:
  get:
    summary: Get a <?= $entity->label; ?> by ID
    responses:
      '200':
        description: Single of <?= $entity->label; ?>

        schema:
          type: object
          properties:
            data:
              $ref: "#/definitions/<?= $entity->class; ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
                  - $ref: '#/definitions/<?= $relationship->class; ?>Relationship'
<?php endif; ?>
<?php endforeach; ?>

/<?= $entity->route; ?>/create:
  post:
    summary: Create a <?= $entity->label; ?>

    responses:
      '200':
        description: New <?= $entity->label; ?>

        schema:
          type: object
          properties:
            data:
              $ref: "#/definitions/<?= $entity->class; ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
                  - $ref: '#/definitions/<?= $relationship->class; ?>Relationship'
<?php endif; ?>
<?php endforeach; ?>

/<?= $entity->route; ?>/update/{id}:
  post:
    summary: Update a <?= $entity->label; ?>

    responses:
      '200':
        description: Updated <?= $entity->label; ?>

        schema:
          type: object
          properties:
            data:
              $ref: "#/definitions/<?= $entity->class; ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
                  - $ref: '#/definitions/<?= $relationship->class; ?>Relationship'
<?php endif; ?>
<?php endforeach; ?>

/<?= $entity->route; ?>/delete/{id}:
  post:
    summary: Delete a <?= $entity->label; ?>

    responses:
      '200':
        description: Deleted <?= $entity->label; ?>

        schema:
          type: object
          properties:
            data:
              $ref: "#/definitions/<?= $entity->class; ?>"
            included:
              type: array
              items:
                anyOf:
<?php foreach ($entity->children('has-many', 'has-one', 'belongs-to') as $relationship): ?>
<?php if ($entity == $relationship->getFrom()): ?>
                  - $ref: '#/definitions/<?= $relationship->class; ?>Relationship'
<?php endif; ?>
<?php endforeach; ?>

<?php endforeach; ?>
