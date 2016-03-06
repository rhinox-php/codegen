<?php
namespace Rhino\Codegen\Template;

class Pdo extends Template {
    
    protected $name = 'pdo';

    public function generate() {
        $this->createFiles([
            $this->path . '/private/scripts/application.js',
            $this->path . '/private/styles/layout.scss',
            $this->path . '/private/styles/tags.scss',
            $this->path . '/private/styles/mixins.scss',
            $this->path . '/private/styles/variables.scss',
        ]);
//        $this->renderTemplate('private/styles/application', $this->path . '/private/styles/application.scss');
//        $this->renderTemplate('bower', $this->path . '/bower.json');
//        $this->renderTemplate('gulpfile', $this->path . '/gulpfile.js');
//        $this->renderTemplate('package', $this->path . '/package.json');
//        $this->renderTemplate('include', $this->path . '/include.php');
        $this->renderTemplate('generated.xml', $this->path . '/generated.xml');
        $this->renderTemplate('bin/router', $this->path . '/bin/router.php');
        $this->renderTemplate('bin/server', $this->path . '/bin/server.bat');
//        $this->renderTemplate('composer', $this->path . '/composer.json');
//        $this->renderTemplate('environment/local', $this->path . '/environment/local.php');
        $this->renderTemplate('sql/create-database', $this->path . '/sql/create-database.sql');
        $this->renderTemplate('views/home', $this->path . $this->getViewPathPrefix() . '/home.php');
        $this->renderTemplate('views/layouts/default', $this->path . $this->getViewPathPrefix() . '/layouts/default.php');
//        $this->renderTemplate('classes/home-controller', $this->path . $this->getClassPathPrefix() . '/Controller/HomeController.php');
//        $this->renderTemplate('classes/application', $this->path . $this->getClassPathPrefix() . '/Application.php');
//        $this->renderTemplate('public/index', $this->path . '/public/index.php', [
//            'entities' => $this->entities,
//        ]);

        $this->renderTemplate('tests/index.js', $this->path . '/tests/index.js');

        $this->renderTemplate('tests/api.js', $this->path . '/tests/api.js');

        foreach ($this->entities as $entity) {
            $this->renderTemplate('classes/model', $this->path . $this->getClassPathPrefix() . '/Model/' . $entity->getClassName() . '.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('classes/controller', $this->path . $this->getClassPathPrefix() . '/Controller/' . $entity->getClassName() . 'Controller.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('views/model/index', $this->path . $this->getViewPathPrefix() . '/' . $entity->getFileName() . '/index.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('views/model/form', $this->path . $this->getViewPathPrefix() . '/' . $entity->getFileName() . '/form.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('sql/full/create-table', $this->path . '/sql/full/' . $entity->getTableName() . '.sql', [
                'entity' => $entity,
            ]);

            $this->renderTemplate('tests/api/model.js', $this->path . '/tests/api/' . $entity->getFileName() . '.js', [
                'entity' => $entity,
            ]);

            foreach ($entity->getRelationships() as $relationship) {
                if ($relationship instanceof Relationship\ManyToMany) {
                    $this->renderTemplate('sql/full/create-relationship-table', $this->path . '/sql/full/' . $entity->getTableName() . '_' . $relationship->getTo()->getTableName() . '.sql', [
                        'entity' => $entity,
                        'relationship' => $relationship,
                    ]);
                }
            }
        }
    }
}
