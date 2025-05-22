<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\Backend\Moderator\ModeraterDashboardController;
use App\Controllers\Backend\Moderator\ModeratorSettingsController;
use App\Controllers\Backend\Admin\AdminDashboardController;
use Slim\App;

return function (App $app) {

    $app->group('/moderate', function ($group)  {
        $group->get('/requests', [ModeraterDashboardController::class, 'showDashboard']);
        $group->get('/settings', [ModeratorSettingsController::class, 'showSettings']);
        $group->post('/settings', [ModeratorSettingsController::class, 'updateSettings']);
        $group->post('/approve', [ModeraterDashboardController::class, 'approvePrayer']);
        $group->post('/deny', [ModeraterDashboardController::class, 'denyPrayer']);        
    })->add(new AuthMiddleware());

    $app->group('/admin', function ($group)  {
        $group->get('/dashboard', [AdminDashboardController::class, 'dashboard']);
    })->add(new AuthMiddleware());
};