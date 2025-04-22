<?php

use Slim\App;

return function (App $app) {
    $app->get('/', function ($request, $response, $args) {
        $response->getBody()->write("Hello, PrayWall!");
        return $response;
    });
};