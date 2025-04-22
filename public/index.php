<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/settings.php';

$app = AppFactory::create();

// Dynamically load all route files
$routesPath = __DIR__ . '/../src/routes';
foreach (glob($routesPath . '/*.php') as $routeFile) {
    (require $routeFile)($app);
}

// Custom 404 handler
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    Slim\Exception\HttpNotFoundException::class,
    function (Request $request, Throwable $exception, bool $displayErrorDetails) use ($app) {
        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write('<h1>Page Not Found</h1><p>The page you are looking for does not exist.</p>');
        return $response->withStatus(404);
    }
);

$app->run();