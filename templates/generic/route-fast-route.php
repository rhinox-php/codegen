<?= '<?php'; ?>

$dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $router) {
<?php foreach ($router->sort()->getRoutes() as $route): ?>
<?php foreach ($route->getHttpMethods() as $httpMethod): ?>
    $router->addRoute('<?= strtoupper($httpMethod); ?>', '<?= $route->getUrlPath(); ?>', [<?= $route->getControllerClass(); ?>::class, '<?= $route->getControllerMethod(); ?>']);
<?php endforeach; ?>
<?php endforeach; ?>

<?php foreach ($this->codegen->getTemplates() as $template): ?>
<?php foreach ($template->iterateRoutes() as [$method, $url, $controller, $function]): ?>
    $router->addRoute('<?= strtoupper($method); ?>', '<?= $url; ?>', [<?= $controller; ?>::class, '<?= $function; ?>']);
<?php endforeach; ?>
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

        assert(class_exists($controllerName), new \Exception('Expected controller class to exist: ' . $controllerName));

        $controller = new $controllerName();

        assert(method_exists($controller, $methodName), new \Exception('Expected controller method to exist: ' . $methodName . ' on class ' . $controllerName));

        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setInput(new \Rhino\InputData\InputData(array_merge(
            $request->query->all(),
            $request->request->all(),
            json_decode($request->getContent(), true) ?: []
        )));
        call_user_func_array([$controller, $methodName], $parameters);
        $response->process();
        break;
    }
}
