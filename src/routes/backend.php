<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\Backend\Moderator\DashboardController as ModeraterDashboardController;
use App\Controllers\Backend\Moderator\SettingsController as ModeratorSettingsController;
use App\Controllers\Backend\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Backend\Admin\UserController as AdminUserController;
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
        $group->get('/users', [AdminUserController::class, 'listUsers']);
        $group->get('/user/{id}/edit', [AdminUserController::class, 'showUserEditForm']);
        $group->post('/user/{id}/edit', [AdminUserController::class, 'updateUser']);
        $group->post('/user/{id}/delete', [AdminUserController::class, 'deleteUser']);
        $group->get('/user/create', [AdminUserController::class, 'showUserCreateForm']);
        $group->post('/user/create', [AdminUserController::class, 'createUser']);
        $group->get('/settings', [AdminDashboardController::class, 'showSettings']);
    })->add(new AuthMiddleware());
};