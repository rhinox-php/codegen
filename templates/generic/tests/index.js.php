<?php foreach ($entities as $entity): ?>
// <?= $entity->getName(); ?>

require('./api/<?= $entity->getFileName(); ?>.js');

<?php endforeach; ?>
