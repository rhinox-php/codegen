<?php
namespace Rhino\Codegen\Template\Admin;

class Controller extends \Rhino\Codegen\Template\Admin
{
    public function generate()
    {
        $this->codegen->composer->addDependency('rhinox/data-table', 'dev-master');
        $this->codegen->composer->addDependency('symfony/validator', '~3.3');

        $this->codegen->composer->addRepository([
            'type' => 'vcs',
            'url' => 'git@bitbucket.org:rhino-php/rhino-data-table',
        ]);
        $this->codegen->gulp->addTask('admin-css', "
            const files = [
                'src/assets/scss/admin.scss',
            ];
            return gulp.src(files)
                .pipe(expectFile(files))
                .pipe(sass())
                .pipe(gulp.dest('public/assets/build/'));
        ");

        $this->renderTemplate('admin/classes/controller-abstract', 'src/classes/Controller/Admin/Generated/AbstractController.php');
        foreach ($this->codegen->node->children('entity') as $entity) {
            $this->renderTemplate('admin/classes/controller', 'src/classes/Controller/Admin/Generated/' . $entity->class . 'AdminController.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('admin/classes/controller-initial', 'src/classes/Controller/Admin/' . $entity->class . 'AdminController.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('admin/classes/data-table', 'src/classes/Controller/Admin/DataTable/' . $entity->class . 'DataTable.php', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('admin/views/form.twig', 'src/views/admin/' . $entity->getFileName() . '/form.twig', [
                'entity' => $entity,
            ]);
            $this->renderTemplate('admin/views/index.twig', 'src/views/admin/' . $entity->getFileName() . '/index.twig', [
                'entity' => $entity,
            ]);
        }
        $this->renderTemplate('admin/views/layout.twig', 'src/views/admin/layout.twig', [
            'entities' => $this->codegen->node->children('entity'),
        ]);
        $this->renderTemplate('admin/assets/admin.scss', 'src/assets/scss/admin.scss', [
        ]);
    }

    public function iterateRoutes()
    {
        foreach ($this->codegen->node->children('entity') as $entity) {
            yield ['get', '/admin/' . $entity->getPluralRouteName(), $this->getNamespace('controller-admin-implemented') . '\\' . $entity->class . 'AdminController', 'index'];
            yield ['post', '/admin/' . $entity->getPluralRouteName(), $this->getNamespace('controller-admin-implemented') . '\\' . $entity->class . 'AdminController', 'index'];
            yield ['get', '/admin/' . $entity->route . '/create', $this->getNamespace('controller-admin-implemented') . '\\' . $entity->class . 'AdminController', 'create'];
            yield ['post', '/admin/' . $entity->route . '/create', $this->getNamespace('controller-admin-implemented') . '\\' . $entity->class . 'AdminController', 'create'];
            yield ['get', '/admin/' . $entity->route . '/{id}', $this->getNamespace('controller-admin-implemented') . '\\' . $entity->class . 'AdminController', 'edit'];
            yield ['post', '/admin/' . $entity->route . '/{id}', $this->getNamespace('controller-admin-implemented') . '\\' . $entity->class . 'AdminController', 'edit'];
            // yield ['post', '/admin/' . $entity->route . '/delete/{id}', $this->getNamespace('controller-admin-implemented') . '\\' . $entity->class . 'AdminController', 'delete'];
        }
    }
}
