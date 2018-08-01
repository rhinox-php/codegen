const api = require('../api');
const assert = require('assert');
const faker = require('faker');

let <?= $entity->getPropertyName(); ?> = {
    data: {
        id: null,
        type: '<?= $entity->getClassName(); ?>',
        attributes: {
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isForeignKey()) continue; ?>
<?php if ($attribute->isType(['String'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.random.word(),
<?php elseif ($attribute->isType(['Text'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.random.words(),
<?php elseif ($attribute->isType(['Int'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.random.number(),
<?php elseif ($attribute->isType(['Decimal'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.random.number(),
<?php elseif ($attribute->isType(['Date'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.date.recent(),
<?php elseif ($attribute->isType(['DateTime'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.date.recent(),
<?php endif; ?>
<?php endforeach;?>
        },
    },
};

describe('/api/v1/<?= $entity->getRouteName(); ?>', function () {
    it('should create a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->getRouteName(); ?>/create', <?= $entity->getPropertyName(); ?>);
        }).then(function(response) {
            <?= $entity->getPropertyName(); ?> = response;
            api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should list <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.get('<?= $entity->getRouteName(); ?>/index?page[limit]=10');
        }).then(function(response) {
            api.validateJsonApi(response);
            assert(Array.isArray(response.data), 'Expected index to return an array of data');
            assert(response.data.length <= 10, 'Expected data to be limited to 10');
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should read a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.get('<?= $entity->getRouteName(); ?>/get/' + <?= $entity->getPropertyName(); ?>.data.id);
        }).then(function(response) {
            <?= $entity->getPropertyName(); ?> = response;
            api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should update a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isForeignKey()) continue; ?>
<?php if ($attribute->isType(['String'])): ?>
            <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?> = faker.random.word();
<?php elseif ($attribute->isType(['Text'])): ?>
            <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?> = faker.random.words();
<?php elseif ($attribute->isType(['Int'])): ?>
            <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?> = faker.random.number();
<?php elseif ($attribute->isType(['Decimal'])): ?>
            <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?> = faker.random.number();
<?php elseif ($attribute->isType(['Date'])): ?>
            <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?> = faker.date.recent();
<?php elseif ($attribute->isType(['DateTime'])): ?>
            <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?> = faker.date.recent();
<?php endif; ?>
<?php endforeach;?>
            return api.post('<?= $entity->getRouteName(); ?>/update/' + <?= $entity->getPropertyName(); ?>.data.id, <?= $entity->getPropertyName(); ?>);
        }).then(function(response) {
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->isForeignKey()) continue; ?>
<?php if ($attribute->isType(['String'])): ?>
            assert.equal(response.data.attributes.<?= $attribute->getPropertyName(); ?>, <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?>, 'Expected <?= $attribute->getPropertyName(); ?> to be updated');
<?php elseif ($attribute->isType(['Text'])): ?>
            assert.equal(response.data.attributes.<?= $attribute->getPropertyName(); ?>, <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?>, 'Expected <?= $attribute->getPropertyName(); ?> to be updated');
<?php elseif ($attribute->isType(['Int'])): ?>
            assert.equal(response.data.attributes.<?= $attribute->getPropertyName(); ?>, <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?>, 'Expected <?= $attribute->getPropertyName(); ?> to be updated');
<?php elseif ($attribute->isType(['Decimal'])): ?>
            assert.equal(response.data.attributes.<?= $attribute->getPropertyName(); ?>, <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?>, 'Expected <?= $attribute->getPropertyName(); ?> to be updated');
<?php elseif ($attribute->isType(['Date'])): ?>
            assert.equal(response.data.attributes.<?= $attribute->getPropertyName(); ?>, <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?>, 'Expected <?= $attribute->getPropertyName(); ?> to be updated');
<?php elseif ($attribute->isType(['DateTime'])): ?>
            assert.equal(response.data.attributes.<?= $attribute->getPropertyName(); ?>, <?= $entity->getPropertyName(); ?>.data.attributes.<?= $attribute->getPropertyName(); ?>, 'Expected <?= $attribute->getPropertyName(); ?> to be updated');
<?php endif; ?>
<?php endforeach;?>
            <?= $entity->getPropertyName(); ?> = response;
            api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should fail to update a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->getRouteName(); ?>/update/' + api.idNotFound, {}, 404);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should delete a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->getRouteName(); ?>/delete/' + <?= $entity->getPropertyName(); ?>.data.id, <?= $entity->getPropertyName(); ?>);
        }).then(function(response) {
            <?= $entity->getPropertyName(); ?> = response;
            api.validateJsonApi(response);
        }).then(function() {
            return api.get('<?= $entity->getRouteName(); ?>/get/' + <?= $entity->getPropertyName(); ?>.data.id, null, 404);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should fail to delete a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->getRouteName(); ?>/delete/' + api.idNotFound, null, 404);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });
});
