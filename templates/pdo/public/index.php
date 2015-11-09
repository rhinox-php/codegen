<?= '<?php'; ?>

require_once __DIR__ . '/../include.php';

$dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', ['<?= $codegen->getNamespace(); ?>\Controller\HomeController', 'home']);

    <?php foreach ($entities as $entity): ?>

    $r->addRoute('GET', "/<?= $entity->getPluralRouteName(); ?>", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getName(); ?>Controller", 'index']);
    $r->addRoute('POST', "/<?= $entity->getPluralRouteName(); ?>", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getName(); ?>Controller", 'index']);
    $r->addRoute('GET', "/<?= $entity->getRouteName(); ?>/create", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getName(); ?>Controller", 'create']);
    $r->addRoute('POST', "/<?= $entity->getRouteName(); ?>/create", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getName(); ?>Controller", 'create']);
    $r->addRoute('GET', "/<?= $entity->getRouteName(); ?>/edit/{id}", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getName(); ?>Controller", 'edit']);
    $r->addRoute('POST', "/<?= $entity->getRouteName(); ?>/edit/{id}", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getName(); ?>Controller", 'edit']);
    $r->addRoute('POST', "/<?= $entity->getRouteName(); ?>/delete/{id}", ["<?= $codegen->getNamespace(); ?>\Controller\<?= $entity->getName(); ?>Controller", 'delete']);
    <?php endforeach; ?>
});

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
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
        $controller = new $controllerName($request, $application);
        if (!method_exists($controller, 'filterRoute') || !$controller->filterRoute($methodName, $parameters)) {
            call_user_func_array([$controller, $methodName], $parameters);
        }
        break;
    }
}
