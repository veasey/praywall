<?php
// The door to the Prayer Wall

session_start();

use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use App\Middleware\ErrorHandlerMiddleware;
use App\Middleware\TwigGlobalsMiddleware;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/settings.php';   // loads Dotenv into $_ENV

$container = require __DIR__ . '/../src/container.php';
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->add(TwigMiddleware::createFromContainer($app, \Slim\Views\Twig::class));
$app->add(TwigGlobalsMiddleware::class);
$app->add(ErrorHandlerMiddleware::class);

// Register routes — each route a new opportunity to serve the community and bring others closer to the Lord
$routesPath = __DIR__ . '/../src/routes';
foreach (glob($routesPath . '/*.php') as $routeFile) {
    (require $routeFile)($app);
}

// Handle errors with grace — "For the Lord is close to the brokenhearted" (Psalm 34:18)
// When someone loses their way, we help them find it and walk them home
require __DIR__ . '/../src/errorHandler.php';

$app->run();