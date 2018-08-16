{% extends 'admin/layout.twig' %}
{% block title %}<?= $entity->label; ?>{% endblock %}
{% block content %}
    <form action="" method="post" class="form-horizontal">
        <div class="cga-body-header">
            <div class="clearfix">
                <div class="pull-left">
                    <div class="cga-body-header-title"><?= $entity->label; ?></div>
                    <div class="cga-body-header-bread-crumbs">Bread / Crumbs</div>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>

        <div class="cga-body-content">
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->is('date')): ?>
            <div class="form-group">
                <label class="col-md-2"><?= $attribute->label; ?></label>
                <div class="col-md-10">
                    <input type="date" class="form-control" name="<?= $attribute->property; ?>" value="{{ <?= $entity->property; ?>.get<?= $attribute->method; ?>() }}" placeholder="<?= $attribute->label; ?>" />
                </div>
            </div>
<?php elseif ($attribute->is('bool')): ?>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-10">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="<?= $attribute->property; ?>" {{ <?= $entity->property; ?>.get<?= $attribute->method; ?>() ? 'checked' : '' }} placeholder="<?= $attribute->label; ?>" />
                            <?= $attribute->label; ?>
                        </label>
                    </div>
                </div>
            </div>
<?php else: ?>
            <div class="form-group">
                <label class="col-md-2"><?= $attribute->label; ?></label>
                <div class="col-md-10">
                    <input type="text" class="form-control" name="<?= $attribute->property; ?>" value="{{ <?= $entity->property; ?>.get<?= $attribute->method; ?>() }}" placeholder="<?= $attribute->label; ?>" />
                </div>
            </div>
<?php endif; ?>
<?php endforeach; ?>

            <div class="form-group">
            </div>
        </div>
    </form>
{% endblock %}
