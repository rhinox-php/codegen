swagger: '2.0'
info:
  title: <?= $jsonApi->getTitle(); ?>

  version: <?= $jsonApi->getVersion(); ?>

  contact:
    email: <?= $jsonApi->getEmail(); ?>

schemes:
  - https
host: <?= $jsonApi->getHost(); ?>

basePath: <?= $jsonApi->getBasePath(); ?>

produces:
  - application/vnd.api+json
paths:
  $ref: ./paths.yml
definitions:
  $ref: ./definitions.yml