<div class="panel panel-default">
    <div class="panel-heading">
        <h1><?= $entity->getLabel(); ?></h1>
    </div>
    <div class="panel-body">
        <?= '<?= $dataTable->raw()->render(); ?>'; ?>

        <a href="/<?= $entity->getRouteName(); ?>/create" class="btn btn-primary">Create</a>
    </div>
</div>