<?php foreach ($entities as $entity): ?>
// <?= $entity->getName(); ?>

var entity = require('./api/<?= $entity->getFileName(); ?>-create.js')();
require('./api/<?= $entity->getFileName(); ?>-read.js')(entity);
require('./api/<?= $entity->getFileName(); ?>-update.js')(entity);
require('./api/<?= $entity->getFileName(); ?>-delete.js')(entity);

<?php endforeach; ?>
