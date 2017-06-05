const api = require('../api');
const faker = require('faker');

let <?= $entity->getPropertyName(); ?> = {
    data: {
        id: null,
        type: '<?= $entity->getClassName(); ?>',
        attributes: {
<?php foreach ($entity->getAttributes() as $attribute): ?>
<?php if ($attribute->is(['String'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.random.word(),
<?php elseif ($attribute->is(['Text'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.random.words(),
<?php elseif ($attribute->is(['Int'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.random.number(),
<?php elseif ($attribute->is(['Decimal'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.random.number(),
<?php elseif ($attribute->is(['Date'])): ?>
            <?= $attribute->getPropertyName(); ?>: faker.date.recent(),
<?php elseif ($attribute->is(['DateTime'])): ?>
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
            return api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should read a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.get('<?= $entity->getRouteName(); ?>/get/' + <?= $entity->getPropertyName(); ?>.data.id);
        }).then(function(response) {
            <?= $entity->getPropertyName(); ?> = response;
            return api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should update a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            // @todo mod entity
            return api.post('<?= $entity->getRouteName(); ?>/update/' + <?= $entity->getPropertyName(); ?>.data.id, <?= $entity->getPropertyName(); ?>);
        }).then(function(response) {
            // @todo assert mods
    //        assert.equal(contact.data.attributes.firstName, name);
            <?= $entity->getPropertyName(); ?> = response;
            return api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });

    it('should delete a <?= $entity->getName(); ?>', function (done) {
        api.auth().then(function() {
            return api.post('<?= $entity->getRouteName(); ?>/delete/' + <?= $entity->getPropertyName(); ?>.data.id, <?= $entity->getPropertyName(); ?>);
        }).then(function(response) {
            <?= $entity->getPropertyName(); ?> = response;
            return api.validateJsonApi(response);
        }).then(function() {
            done();
        }).catch(api.handleError(done));
    });
});
