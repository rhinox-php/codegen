<?= '<?php'; ?>

require_once __DIR__ . '/../include.php';

$dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $router) {
    $router->addRoute('GET', '/', ['<?= $codegen->getNamespace(); ?>\Controller\HomeController', 'home']);

    <?php foreach ($entities as $entity): ?>

    $router->addRoute('GET', "<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getPluralRouteName(); ?>", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'index']);
    $router->addRoute('POST', "<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getPluralRouteName(); ?>", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'index']);
    $router->addRoute('GET', "<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/create", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'create']);
    $router->addRoute('POST', "<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/create", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'create']);
    $router->addRoute('GET', "<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/edit/{id}", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'edit']);
    $router->addRoute('POST', "<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/edit/{id}", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'edit']);
    $router->addRoute('POST', "<?= $codegen->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/delete/{id}", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'delete']);
    <?php endforeach; ?>
    
    require __DIR__ . '/../routes.php';
});

$request = \Rhino\Core\Http\Request::createFromGlobals();
$response = new \Rhino\Core\Http\Response();
$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
switch ($routeInfo[0]) {
    case \FastRoute\Dispatcher::NOT_FOUND: {
        http_response_code(404);
        echo htmlspecialchars($request->getPathInfo()) . ' not found';
        break;
    }
    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED: {
        http_response_code(405);
        echo htmlspecialchars($request->getMethod()) . ' method not allowed';
        break;
    }
    case \FastRoute\Dispatcher::FOUND: {
        list($status, $handler, $parameters) = $routeInfo;
        list($controllerName, $methodName) = $handler;
        $controller = new $controllerName();
        $controller->setRequest($request);
        $controller->setResponse($response);
        if (!$controller->filterRoute($methodName, $parameters)) {
            call_user_func_array([$controller, $methodName], $parameters);
        }
        $response->process();
        break;
    }
}
