<?php

use Slim\App;

return function (App $app) {
    $app->get('/migrate', function ($request, $response, $args) {
        if (getenv('APP_ENV') !== 'development') {
            return $response->withStatus(403)->write('Forbidden');
        }

        include __DIR__ . '/../setup/migrate.php';
        $response->getBody()->write("Migration completed successfully!");
        return $response;
    });
};