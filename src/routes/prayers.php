<?php

use Slim\App;
use App\Controllers\PrayerController;

return function (App $app) {

    $controller = $app->getContainer()->get(PrayerController::class);

    $app->get('/', [$controller, 'listPrayers']);
    $app->get('/prayers', [$controller, 'listPrayers']);
    
    $app->get('/prayers/request', [$controller, 'prayerRequest']);
    $app->post('/prayers/request', [$controller, 'prayerRequest']);
};