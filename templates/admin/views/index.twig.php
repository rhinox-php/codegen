{% extends 'admin/layout.twig' %}
{% block title %}<?= $entity->getPluralLabel(); ?>{% endblock %}
{% block content %}
    <div class="cga-body-header">
        <div class="clearfix">
            <div class="pull-left">
                <div class="cga-body-header-title"><?= $entity->getPluralLabel(); ?></div>
                <div class="cga-body-header-bread-crumbs">Bread / Crumbs</div>
            </div>
            <div class="pull-right">
                <a href="/admin/<?= $entity->route; ?>/create" class="btn btn-primary">Create</a>
            </div>
        </div>
    </div>

    <div class="cga-body-content">
        {{ dataTable.render() | raw }}
    </div>
{% endblock %}
