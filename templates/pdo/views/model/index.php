<div class="panel panel-default">
    <div class="panel-heading">
        <h1><?= $entity->getLabel(); ?></h1>
    </div>
    <div class="panel-body">
        <?= '<?= $table->raw()->render(); ?>'; ?>

        <a href="/contact/create" class="btn btn-primary">Create</a>
    </div>
</div>