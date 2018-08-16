const api = require('../api');
const assert = require('assert');
const faker = require('faker');

let <?= $entity->property; ?> = {
    data: {
        id: null,
        type: '<?= $entity->class; ?>',
        attributes: {
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->isForeignKey()) continue; ?>
<?php if ($attribute->is('string')): ?>
            <?= $attribute->property; ?>: faker.random.word(),
<?php elseif ($attribute->is('text')): ?>
            <?= $attribute->property; ?>: faker.random.words(),
<?php elseif ($attribute->is('int')): ?>
            <?= $attribute->property; ?>: faker.random.number(),
<?php elseif ($attribute->is('decimal')): ?>
            <?= $attribute->property; ?>: faker.random.number(),
<?php elseif ($attribute->is('date')): ?>
            <?= $attribute->property; ?>: faker.date.recent(),
<?php elseif ($attribute->is('date-time')): ?>
            <?= $attribute->property; ?>: faker.date.recent(),
<?php endif; ?>
<?php endforeach;?>
        },
    },
};

describe('/api/v1/<?= $entity->route; ?>', function () {
    it('should create a <?= $entity->name; ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->route; ?>/create', <?= $entity->property; ?>);
        }).then(function(response) {
            <?= $entity->property; ?> = response;
            api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should list <?= $entity->name; ?>', function (done) {
        api.auth().then(function() {
            return api.get('<?= $entity->route; ?>/index?page[limit]=10');
        }).then(function(response) {
            api.validateJsonApi(response);
            assert(Array.isArray(response.data), 'Expected index to return an array of data');
            assert(response.data.length <= 10, 'Expected data to be limited to 10');
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should read a <?= $entity->name; ?>', function (done) {
        api.auth().then(function() {
            return api.get('<?= $entity->route; ?>/get/' + <?= $entity->property; ?>.data.id);
        }).then(function(response) {
            <?= $entity->property; ?> = response;
            api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should update a <?= $entity->name; ?>', function (done) {
        api.auth().then(function() {
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->isForeignKey()) continue; ?>
<?php if ($attribute->is('string')): ?>
            <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?> = faker.random.word();
<?php elseif ($attribute->is('text')): ?>
            <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?> = faker.random.words();
<?php elseif ($attribute->is('int')): ?>
            <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?> = faker.random.number();
<?php elseif ($attribute->is('decimal')): ?>
            <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?> = faker.random.number();
<?php elseif ($attribute->is('date')): ?>
            <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?> = faker.date.recent();
<?php elseif ($attribute->is('date-time')): ?>
            <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?> = faker.date.recent();
<?php endif; ?>
<?php endforeach;?>
            return api.post('<?= $entity->route; ?>/update/' + <?= $entity->property; ?>.data.id, <?= $entity->property; ?>);
        }).then(function(response) {
<?php foreach ($entity->children('string', 'int', 'decimal', 'date', 'date-time', 'bool', 'text') as $attribute): ?>
<?php if ($attribute->isForeignKey()) continue; ?>
<?php if ($attribute->is('string')): ?>
            assert.equal(response.data.attributes.<?= $attribute->property; ?>, <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?>, 'Expected <?= $attribute->property; ?> to be updated');
<?php elseif ($attribute->is('text')): ?>
            assert.equal(response.data.attributes.<?= $attribute->property; ?>, <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?>, 'Expected <?= $attribute->property; ?> to be updated');
<?php elseif ($attribute->is('int')): ?>
            assert.equal(response.data.attributes.<?= $attribute->property; ?>, <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?>, 'Expected <?= $attribute->property; ?> to be updated');
<?php elseif ($attribute->is('decimal')): ?>
            assert.equal(response.data.attributes.<?= $attribute->property; ?>, <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?>, 'Expected <?= $attribute->property; ?> to be updated');
<?php elseif ($attribute->is('date')): ?>
            assert.equal(response.data.attributes.<?= $attribute->property; ?>, <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?>, 'Expected <?= $attribute->property; ?> to be updated');
<?php elseif ($attribute->is('date-time')): ?>
            assert.equal(response.data.attributes.<?= $attribute->property; ?>, <?= $entity->property; ?>.data.attributes.<?= $attribute->property; ?>, 'Expected <?= $attribute->property; ?> to be updated');
<?php endif; ?>
<?php endforeach;?>
            <?= $entity->property; ?> = response;
            api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should fail to update a <?= $entity->name; ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->route; ?>/update/' + api.idNotFound, {}, 404);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should delete a <?= $entity->name; ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->route; ?>/delete/' + <?= $entity->property; ?>.data.id, <?= $entity->property; ?>);
        }).then(function(response) {
            <?= $entity->property; ?> = response;
            api.validateJsonApi(response);
        }).then(function() {
            return api.get('<?= $entity->route; ?>/get/' + <?= $entity->property; ?>.data.id, null, 404);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should fail to delete a <?= $entity->name; ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->route; ?>/delete/' + api.idNotFound, null, 404);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });
});
