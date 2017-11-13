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

/<?= $entity->getRouteName(); ?>/get/{id}:
  get:
    summary: Get a <?= $entity->getLabel(); ?> by ID
    responses:
      '200':
        description: Single of <?= $entity->getLabel(); ?>

/<?= $entity->getRouteName(); ?>/create:
  post:
    summary: Create a <?= $entity->getLabel(); ?>

    responses:
      '200':
        description: New <?= $entity->getLabel(); ?>

/<?= $entity->getRouteName(); ?>/update/{id}:
  post:
    summary: Update a <?= $entity->getLabel(); ?>

    responses:
      '200':
        description: Updated <?= $entity->getLabel(); ?>

/<?= $entity->getRouteName(); ?>/delete/{id}:
  post:
    summary: Delete a <?= $entity->getLabel(); ?>

    responses:
      '200':
        description: Deleted <?= $entity->getLabel(); ?>

<?php endforeach; ?>
