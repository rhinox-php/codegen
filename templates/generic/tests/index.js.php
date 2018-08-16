<?php foreach ($entities as $entity): ?>
// <?= $entity->name; ?>

require('./api/<?= $entity->getFileName(); ?>.js');

<?php endforeach; ?>
