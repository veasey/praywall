<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\Backend\ModeraterDashboardController;
use App\Controllers\Backend\AdminDashboardController;
use Slim\App;

return function (App $app) {

    $app->group('/moderate', function ($group)  {
        $group->get('/dashboard', [ModeraterDashboardController::class, 'dashboard']);
        $group->post('/approve', [ModeraterDashboardController::class, 'approvePrayer']);
        $group->post('/deny', [ModeraterDashboardController::class, 'denyPrayer']);
    })->add(new AuthMiddleware());

    $app->group('/admin', function ($group)  {
        $group->get('/dashboard', [AdminDashboardController::class, 'dashboard']);
    })->add(new AuthMiddleware());
};