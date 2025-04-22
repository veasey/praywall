<?php

use Slim\App;

return function (App $app) {
    $app->get('/migrate', function ($request, $response, $args) {

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..'); 
        $dotenv->load();
        $environment = $_ENV['APP_ENV'];

        if ($environment !== 'development') {
            $response->getBody()->write('Forbidden');
            return $response->withStatus(403);
        }

        include __DIR__ . '/../setup/migrate.php';
        $response->getBody()->write("Migration completed successfully!");
        return $response;
    });
};