<div class="panel panel-default">
    <div class="panel-heading">
        <h1><?= $entity->label; ?></h1>
    </div>
    <div class="panel-body">
        <?= '<?= $dataTable->raw()->render(); ?>'; ?>

        <a href="/<?= $entity->route; ?>/create" class="btn btn-primary">Create</a>
    </div>
</div>