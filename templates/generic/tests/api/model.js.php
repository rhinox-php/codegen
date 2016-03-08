var api = require('../api.js');

var entity = {
    id: null,
<?php foreach ($entity->getAttributes() as $attribute): ?>
    <?= $attribute->getPropertyName(); ?>: null,
<?php endforeach;?>
};

describe('/api/v1/<?= $entity->getRouteName(); ?>', function () {
    it('should create a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->getRouteName(); ?>/create', entity);
        }).then(function(body) {
            return api.validateJsonApi(body);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should read a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.get('<?= $entity->getRouteName(); ?>/' + entity.id);
        }).then(function(body) {
            return api.validateJsonApi(body);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should update a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            // @todo mod entity
            return api.post('<?= $entity->getRouteName(); ?>/edit/' + entity.id, entity);
        }).then(function(entity) {
            // @todo assert mods
    //        assert.equal(contact.data.attributes.firstName, name);
            return api.validateJsonApi(entity);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should delete a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->getRouteName(); ?>/delete/' + entity.id, entity);
        }).then(function(entity) {
            return api.validateJsonApi(entity);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });
});
