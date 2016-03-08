<?php
namespace Rhino\Codegen\Template;

class Generic extends Template {
    
    protected $name = 'generic';
    protected $viewPath = 'views';
    protected $controllerPath = 'classes/Controller';
    protected $modelPath = 'classes/Model/Generated';
    protected $modelNamespace = 'App\Model\Generated';
    protected $modelImplementationNamespace = 'App\Model';

    public function generate() {
//        $this->createFiles([
//            $this->getPath('/private/scripts/application.js'),
//            $this->getPath('/private/styles/layout.scss'),
//            $this->getPath('/private/styles/tags.scss'),
//            $this->getPath('/private/styles/mixins.scss'),
//            $this->getPath('/private/styles/variables.scss'),
//        ]);
////        $this->renderTemplate('private/styles/application', $this->getPath('/private/styles/application.scss'));
////        $this->renderTemplate('bower', $this->getPath('/bower.json'));
////        $this->renderTemplate('gulpfile', $this->getPath('/gulpfile.js'));
////        $this->renderTemplate('package', $this->getPath('/package.json'));
////        $this->renderTemplate('include', $this->getPath('/include.php'));
//        $this->renderTemplate('generated.xml', $this->getPath('/generated.xml'));
//        $this->renderTemplate('bin/router', $this->getPath('/bin/router.php'));
//        $this->renderTemplate('bin/server', $this->getPath('/bin/server.bat'));
////        $this->renderTemplate('composer', $this->getPath('/composer.json'));
////        $this->renderTemplate('environment/local', $this->getPath('/environment/local.php'));
//        $this->renderTemplate('sql/create-database', $this->getPath('/sql/create-database.sql'));
//        $this->renderTemplate('views/home', $this->getPath($this->getViewPath() . '/home.php'));
//        $this->renderTemplate('views/layouts/default', $this->getPath($this->getViewPath() . '/layouts/default.php'));
////        $this->renderTemplate('classes/home-controller', $this->getPath($this->getClassPathPrefix() . '/Controller/HomeController.php'));
////        $this->renderTemplate('classes/application', $this->getPath($this->getClassPathPrefix() . '/Application.php'));
////        $this->renderTemplate('public/index', $this->getPath('/public/index.php', [
////            'entities' => $this->entities,
////        ]));
//
//        $this->renderTemplate('tests/index.js', $this->getPath('/tests/index.js'));
//
//        $this->renderTemplate('tests/api.js', $this->getPath('/tests/api.js'));
//
//        foreach ($this->codegen->getEntities() as $entity) {
//            $this->renderTemplate('classes/model', $this->getModelPath($entity->getClassName() . '.php'), [
//                'entity' => $entity,
//            ]);
//            $this->renderTemplate('classes/controller', $this->getControllerPath($entity->getClassName() . 'Controller.php'), [
//                'entity' => $entity,
//            ]);
//            $this->renderTemplate('views/model/index', $this->getViewPath($entity->getFileName() . '/index.php'), [
//                'entity' => $entity,
//            ]);
//            $this->renderTemplate('views/model/form', $this->getViewPath($entity->getFileName() . '/form.php'), [
//                'entity' => $entity,
//            ]);
//            $this->renderTemplate('sql/full/create-table', $this->getPath('/sql/full/' . $entity->getTableName() . '.sql'), [
//                'entity' => $entity,
//            ]);
//
//            $this->renderTemplate('tests/api/model.js', $this->getPath('/tests/api/' . $entity->getFileName() . '.js'), [
//                'entity' => $entity,
//            ]);
//
//            foreach ($entity->getRelationships() as $relationship) {
//                if ($relationship instanceof Relationship\ManyToMany) {
//                    $this->renderTemplate('sql/full/create-relationship-table', $this->getPath('/sql/full/' . $entity->getTableName() . '_' . $relationship->getTo()->getTableName() . '.sql'), [
//                        'entity' => $entity,
//                        'relationship' => $relationship,
//                    ]);
//                }
//            }
//        }
    }
    
    public function getPath(string $path = ''): string {
        return $this->codegen->getPath($path);
    }
    
    public function getViewPath(string $path = ''): string {
        return ($this->viewPath ?: $this->codegen->getPath('/views/')) . '/' . $path;
    }

    public function setViewPath(): string {
        $this->viewPath = $viewPath;
        return $this;
    }
    
    public function getControllerPath(string $path = ''): string {
        return ($this->controllerPath ?: $this->codegen->getPath('/classes/Controller/')) . '/' . $path;
    }

    public function setControllerPath(string $controllerPath) {
        $this->controllerPath = $controllerPath;
        return $this;
    }

    public function getModelPath(string $path = ''): string {
        return ($this->modelPath ?: $this->codegen->getPath('/classes/Model/')) . '/' . $path;
    }

    public function setModelPath(string $modelPath) {
        $this->modelPath = $modelPath;
        return $this;
    }

    public function getModelNamespace() {
        return $this->modelNamespace;
    }

    public function setModelNamespace($modelNamespace) {
        $this->modelNamespace = $modelNamespace;
        return $this;
    }

    public function getModelImplementationNamespace() {
        return $this->modelImplementationNamespace;
    }

    public function setModelImplementationNamespace($modelImplementationNamespace) {
        $this->modelImplementationNamespace = $modelImplementationNamespace;
        return $this;
    }


}
