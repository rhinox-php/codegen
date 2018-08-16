<?php foreach ($entities as $entity): ?>
<?= $entity->class; ?>:
  $ref: ./definitions/<?= $entity->getFileName(); ?>.yml
<?php endforeach; ?>