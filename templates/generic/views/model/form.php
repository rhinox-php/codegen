<div class="panel panel-default">
    <div class="panel-heading">
        <h1><?= $entity->getLabel(); ?></h1>
    </div>
    <div class="panel-body">
        <form action="" method="post" class="form-horizontal">
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute instanceof \Rhino\Codegen\Attribute\DateAttribute): ?>
            <div class="form-group">
                <label class="col-md-2"><?= $attribute->getLabel(); ?></label>
                <div class="col-md-10">
                    <input type="date" class="form-control" name="<?= $attribute->getPropertyName(); ?>" value="<?= '<?= $' . $entity->getPropertyName() . '->get' . $attribute->getMethodName() . '()->format("Y-m-d")->attr(); ?>'; ?>" placeholder="<?= $attribute->getLabel(); ?>" />
                </div>
            </div>
<?php elseif ($attribute instanceof \Rhino\Codegen\Attribute\BoolAttribute): ?>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-10">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="<?= $attribute->getPropertyName(); ?>" <?= '<?= $' . $entity->getPropertyName() . '->get' . $attribute->getMethodName() . '()->raw() ? "checked" : ""; ?>'; ?> placeholder="<?= $attribute->getLabel(); ?>" />
                            <?= $attribute->getLabel(); ?>
                        </label>
                    </div>
                </div>
            </div>
<?php else: ?>
            <div class="form-group">
                <label class="col-md-2"><?= $attribute->getLabel(); ?></label>
                <div class="col-md-10">
                    <input type="text" class="form-control" name="<?= $attribute->getPropertyName(); ?>" value="<?= '<?= $' . $entity->getPropertyName() . '->get' . $attribute->getMethodName() . '()->attr(); ?>'; ?>" placeholder="<?= $attribute->getLabel(); ?>" />
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