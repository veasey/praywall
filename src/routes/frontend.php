<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\Frontend\PrayerController;
use App\Controllers\Frontend\AuthController;
use App\Controllers\Backend\Moderator\DashboardController as ModeraterDashboardController;
use App\Controllers\Backend\Profile\SettingsController as ProfileSettingsController;
use Slim\App;

return function (App $app) {

    // Home Page    
    $app->get('/', [PrayerController::class, 'listPrayers']);
    $app->group('/prayers', function ($group) {
        $group->get('', [PrayerController::class, 'listPrayers']);
        $group->get('/request', [PrayerController::class, 'prayerRequest'])->add(new AuthMiddleware());
        $group->post('/request', [PrayerController::class, 'prayerRequest'])->add(new AuthMiddleware());
        $group->post('/pray/{id}', [PrayerController::class, 'pray'])->add(new AuthMiddleware());
    });

    // Logon Form
    $app->get('/login', [AuthController::class, 'showLoginForm']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/register', [AuthController::class, 'showRegisterForm']);
    $app->post('/register', [AuthController::class, 'register']);
    $app->get('/logout', [AuthController::class, 'logout'])->add(new AuthMiddleware());

    // profile settings
     $app->group('/profile', function ($group) {
        $group->get('/settings', [ProfileSettingsController::class, 'showProfileSettings'])->add(new AuthMiddleware());
        $group->post('/settings', [ProfileSettingsController::class, 'updateProfileSettings'])->add(new AuthMiddleware());
    });

    // moderator controls
    $app->get('/moderate/unapprove/{id}', [ModeraterDashboardController::class, 'unapprovePrayer']); // Unapprove a prayer request from frontend
};