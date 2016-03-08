<rhino>
<?php foreach ($entities as $entity): ?>
    <controller method="get,post" url="<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getPluralRouteName(); ?>" controller="<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller::index" />
    <controller method="get,post" url="<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/create" controller="<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller::create" />
    <controller method="get,post" url="<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/edit/{id}" controller="<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller::edit" />
    <controller method="post" url="<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/delete/{id}" controller="<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller::delete" />
<?php endforeach; ?>
</rhino>
