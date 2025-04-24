<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\ModerateController;
use Slim\App;


return function (App $app) {

    $controller = $app->getContainer()->get(ModerateController::class);

    $app->group('/moderate', function ($group) use ($controller) {
        $group->get('/dashboard', [$controller, 'dashboard']);
        $group->post('/approve', [$controller, 'approvePrayer']);
        $group->post('/deny', [$controller, 'denyPrayer']);
    })->add(new AuthMiddleware());
};