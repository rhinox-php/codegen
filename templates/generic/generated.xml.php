<rhino>
<?php foreach ($entities as $entity): ?>
    <controller method="get,post" url="<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getPluralRouteName(); ?>" controller="<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->class; ?>Controller::index" />
    <controller method="get,post" url="<?= $codegen->getUrlPrefix(); ?>/<?= $entity->route; ?>/create" controller="<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->class; ?>Controller::create" />
    <controller method="get,post" url="<?= $codegen->getUrlPrefix(); ?>/<?= $entity->route; ?>/edit/{id}" controller="<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->class; ?>Controller::edit" />
    <controller method="post" url="<?= $codegen->getUrlPrefix(); ?>/<?= $entity->route; ?>/delete/{id}" controller="<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->class; ?>Controller::delete" />
<?php endforeach; ?>
</rhino>
