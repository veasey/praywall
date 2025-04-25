<?php

use Slim\App;
use App\Controllers\Frontend\PrayerController;
use App\Controllers\Frontend\AuthController;

return function (App $app) {

    $prayerController = $app->getContainer()->get(PrayerController::class);

    // Home Page    
    $app->get('/', [$prayerController, 'listPrayers']);
    $app->get('/prayers', [$prayerController, 'listPrayers']);
    
    // Prayer Request Form
    $app->get('/prayers/request', [$prayerController, 'prayerRequest']);
    $app->post('/prayers/request', [$prayerController, 'prayerRequest']);

    // Logon Form
    $app->get('/login', [AuthController::class, 'showLoginForm']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/logout', [AuthController::class, 'logout']);
};