<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\Frontend\PrayerController;
use App\Controllers\Frontend\AuthController;
use App\Controllers\Backend\Moderator\DashboardController as ModeraterDashboardController;
use Slim\App;

return function (App $app) {

    // Home Page    
    $app->get('/', [PrayerController::class, 'listPrayers']);
    $app->group('/prayers', function ($group) {
        $group->get('', [PrayerController::class, 'listPrayers']);
        $group->get('/request', [PrayerController::class, 'prayerRequest']);
        $group->post('/request', [PrayerController::class, 'prayerRequest']);
        $group->post('/pray/{id}', [PrayerController::class, 'pray'])->add(new AuthMiddleware());
    });

    // Logon Form
    $app->get('/login', [AuthController::class, 'showLoginForm']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/register', [AuthController::class, 'showRegisterForm']);
    $app->post('/register', [AuthController::class, 'register']);
    $app->get('/logout', [AuthController::class, 'logout']);

    // moderator controls
    $app->get('/moderate/unapprove/{id}', [ModeraterDashboardController::class, 'unapprovePrayer']); // Unapprove a prayer request from frontend
};