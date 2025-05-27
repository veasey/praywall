<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\Backend\Moderator\DashboardController as ModeraterDashboardController;
use App\Controllers\Backend\Moderator\SettingsController as ModeratorSettingsController;
use App\Controllers\Backend\Admin\UserController as AdminUserController;
use Slim\App;

return function (App $app) {

    $app->group('/moderate', function ($group)  {
        $group->get('/requests/prayers', [ModeraterDashboardController::class, 'showPrayerRequests']);
        $group->get('/requests/praises', [ModeraterDashboardController::class, 'showPraiseRequests']);
        $group->get('/settings', [ModeratorSettingsController::class, 'showSettings']);
        $group->post('/settings', [ModeratorSettingsController::class, 'updateSettings']);
        $group->post('/prayer/approve', [ModeraterDashboardController::class, 'approvePrayer']);
        $group->post('/prayer/deny', [ModeraterDashboardController::class, 'denyPrayer']);
        $group->post('/praise/approve', [ModeraterDashboardController::class, 'approvePraise']);
        $group->post('/praise/deny', [ModeraterDashboardController::class, 'denyPraise']);   
    })->add(new AuthMiddleware());

    $app->group('/admin', function ($group)  {
        $group->get('/dashboard', [ModeraterDashboardController::class, 'dashboard']);
        $group->get('/users', [AdminUserController::class, 'listUsers']);
        $group->get('/user/{id}/edit', [AdminUserController::class, 'showUserEditForm']);
        $group->post('/user/{id}/edit', [AdminUserController::class, 'updateUser']);
        $group->post('/user/{id}/delete', [AdminUserController::class, 'deleteUser']);
        $group->get('/user/create', [AdminUserController::class, 'showUserCreateForm']);
        $group->post('/user/create', [AdminUserController::class, 'createUser']);
        $group->get('/settings', [ModeraterDashboardController::class, 'showSettings']);
    })->add(new AuthMiddleware());
};