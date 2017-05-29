<?= '<?php'; ?>

$dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $router) {
    $router->addRoute('GET', '/', ['<?= $this->getImplementedNamespace(); ?>\Controller\HomeController', 'home']);

    <?php foreach ($entities as $entity): ?>

    $router->addRoute('GET', "<?= $this->getUrlPrefix(); ?>/<?= $entity->getPluralRouteName(); ?>", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'index']);
    $router->addRoute('POST', "<?= $this->getUrlPrefix(); ?>/<?= $entity->getPluralRouteName(); ?>", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'index']);
    $router->addRoute('GET', "<?= $this->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/create", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'create']);
    $router->addRoute('POST', "<?= $this->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/create", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'create']);
    $router->addRoute('GET', "<?= $this->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/edit/{id}", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'edit']);
    $router->addRoute('POST', "<?= $this->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/edit/{id}", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'edit']);
    $router->addRoute('POST', "<?= $this->getUrlPrefix(); ?>/<?= $entity->getRouteName(); ?>/delete/{id}", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>Controller", 'delete']);

    $router->addRoute('GET', "<?= $this->getUrlPrefix(); ?>/api/v1/<?= $entity->getPluralRouteName(); ?>", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>ApiController", 'index']);
    $router->addRoute('POST', "<?= $this->getUrlPrefix(); ?>/api/v1/<?= $entity->getRouteName(); ?>/create", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>ApiController", 'create']);
    $router->addRoute('POST', "<?= $this->getUrlPrefix(); ?>/api/v1/<?= $entity->getRouteName(); ?>/get/{id}", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>ApiController", 'edit']);
    $router->addRoute('POST', "<?= $this->getUrlPrefix(); ?>/api/v1/<?= $entity->getRouteName(); ?>/edit/{id}", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>ApiController", 'edit']);
    $router->addRoute('POST', "<?= $this->getUrlPrefix(); ?>/api/v1/<?= $entity->getRouteName(); ?>/delete/{id}", ["<?= $this->getImplementedNamespace(); ?>\Controller\<?= $entity->getClassName(); ?>ApiController", 'delete']);
    <?php endforeach; ?>

});

$request = \Rhino\Http\Request::createDefault();
$response = \Rhino\Http\Response::createDefault();
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
