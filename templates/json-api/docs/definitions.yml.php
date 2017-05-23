<?php foreach ($entities as $entity): ?>
<?= $entity->getClassName(); ?>:
  $ref: ./definitions/<?= $entity->getFileName(); ?>.yml
<?php endforeach; ?>