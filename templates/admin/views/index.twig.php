
{% extends 'admin/layout.twig' %}
{% block content %}
    <h1><?= $entity->getLabel(); ?></h1>

    {{ dataTable.render() | raw }}
{% endblock %}
