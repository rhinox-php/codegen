<div class="panel panel-default">
    <div class="panel-heading">
        <h1><?= $entity->label; ?></h1>
    </div>
    <div class="panel-body">
        <form action="" method="post" class="form-horizontal">
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('date')): ?>
            <div class="form-group">
                <label class="col-md-2"><?= $attribute->label; ?></label>
                <div class="col-md-10">
                    <input type="date" class="form-control" name="<?= $attribute->property; ?>" value="<?= '<?= $' . $entity->property . '->get' . $attribute->method . '()->format("Y-m-d")->attr(); ?>'; ?>" placeholder="<?= $attribute->label; ?>" />
                </div>
            </div>
<?php elseif ($attribute->is('bool')): ?>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-10">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="<?= $attribute->property; ?>" <?= '<?= $' . $entity->property . '->get' . $attribute->method . '()->raw() ? "checked" : ""; ?>'; ?> placeholder="<?= $attribute->label; ?>" />
                            <?= $attribute->label; ?>
                        </label>
                    </div>
                </div>
            </div>
<?php else: ?>
            <div class="form-group">
                <label class="col-md-2"><?= $attribute->label; ?></label>
                <div class="col-md-10">
                    <input type="text" class="form-control" name="<?= $attribute->property; ?>" value="<?= '<?= $' . $entity->property . '->get' . $attribute->method . '()->attr(); ?>'; ?>" placeholder="<?= $attribute->label; ?>" />
                </div>
            </div>
<?php endif; ?>
<?php endforeach; ?>

            <div class="form-group">
                <div class="col-md-10 col-md-offset-2">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="/<?= $entity->getPluralRouteName(); ?>" class="btn btn-link">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>