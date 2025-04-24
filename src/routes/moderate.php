<?php

use App\Controllers\ModerateController;
use Slim\App;

return function (App $app) {

    $controller = $app->getContainer()->get(ModerateController::class);

    $app->get('/moderate', [$controller, 'listPrayers']);

    $app->post('/moderate/approve', [$controller, 'approvePrayer']);
    $app->post('/moderate/deny', [$controller, 'denyPrayer']);
};