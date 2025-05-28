<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\Frontend\PrayerController;
use App\Controllers\Frontend\PraiseController;
use App\Controllers\Frontend\AuthController;
use App\Controllers\Backend\Moderator\ContentReviewController;
use App\Controllers\Backend\Profile\SettingsController as ProfileSettingsController;
use Slim\App;

return function (App $app) {

    // Home Page    
    $app->get('/', [PrayerController::class, 'listPrayers']);
    $app->get('/prayers', [PrayerController::class, 'listPrayers']);
    $app->get('/praises', [PraiseController::class, 'listPraiseReports']);

    // communial prayer
    $app->group('/prayers', function ($group) {
        $group->get('/request', [PrayerController::class, 'prayerRequest']);
        $group->post('/request', [PrayerController::class, 'prayerRequest']);
        $group->post('/pray/{id}', [PrayerController::class, 'pray']);
    })->add(new AuthMiddleware());

    // Praise Reports
    $app->group('/praises', function ($group) {
        $group->get('/report', [PraiseController::class, 'praiseReport']);
        $group->post('/report', [PraiseController::class, 'praiseReport']);
    })->add(new AuthMiddleware());

    // Logon Form
    $app->get('/login', [AuthController::class, 'showLoginForm']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/register', [AuthController::class, 'showRegisterForm']);
    $app->post('/register', [AuthController::class, 'register']);
    $app->get('/logout', [AuthController::class, 'logout'])->add(new AuthMiddleware());

    // profile settings
    $app->group('/profile', function ($group) {
        $group->get('/settings', [ProfileSettingsController::class, 'showProfileSettings']);
        $group->post('/settings', [ProfileSettingsController::class, 'updateProfileSettings']);
    })->add(new AuthMiddleware());

    // moderator controls
    $app->group('/moderate/unapprove', function ($group) {
        $group->get('/prayer/{id}', [ContentReviewController::class, 'unapprovePrayer']);
        $group->get('/praise/{id}', [ContentReviewController::class, 'unapprovePraise']);
    })->add(new AuthMiddleware());
};