{% extends 'admin/layout.twig' %}
{% block title %}<?= $entity->getLabel(); ?>{% endblock %}
{% block content %}
    <form action="" method="post" class="form-horizontal">
        <div class="cga-body-header">
            <div class="clearfix">
                <div class="pull-left">
                    <div class="cga-body-header-title"><?= $entity->getLabel(); ?></div>
                    <div class="cga-body-header-bread-crumbs">Bread / Crumbs</div>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>

        <div class="cga-body-content">
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['Date'])): ?>
            <div class="form-group">
                <label class="col-md-2"><?= $attribute->getLabel(); ?></label>
                <div class="col-md-10">
                    <input type="date" class="form-control" name="<?= $attribute->getPropertyName(); ?>" value="{{ <?= $entity->getPropertyName(); ?>.get<?= $attribute->getMethodName(); ?>() }}" placeholder="<?= $attribute->getLabel(); ?>" />
                </div>
            </div>
<?php elseif ($attribute->is(['Bool'])): ?>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-10">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="<?= $attribute->getPropertyName(); ?>" {{ <?= $entity->getPropertyName(); ?>.get<?= $attribute->getMethodName(); ?>() ? 'checked' : '' }} placeholder="<?= $attribute->getLabel(); ?>" />
                            <?= $attribute->getLabel(); ?>
                        </label>
                    </div>
                </div>
            </div>
<?php else: ?>
            <div class="form-group">
                <label class="col-md-2"><?= $attribute->getLabel(); ?></label>
                <div class="col-md-10">
                    <input type="text" class="form-control" name="<?= $attribute->getPropertyName(); ?>" value="{{ <?= $entity->getPropertyName(); ?>.get<?= $attribute->getMethodName(); ?>() }}" placeholder="<?= $attribute->getLabel(); ?>" />
                </div>
            </div>
<?php endif; ?>
<?php endforeach; ?>

            <div class="form-group">
            </div>
        </div>
    </form>
{% endblock %}
