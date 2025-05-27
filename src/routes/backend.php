<?php

use App\Middleware\AuthMiddleware;
use App\Controllers\Backend\Moderator\ContentReviewController;
use App\Controllers\Backend\Moderator\SettingsController as ModeratorSettingsController;
use App\Controllers\Backend\Admin\UserController as AdminUserController;
use Slim\App;

return function (App $app) {

    $app->group('/moderate', function ($group)  {
        $group->get('/requests/prayers', [ContentReviewController::class, 'showPrayerRequests']);
        $group->get('/requests/praises', [ContentReviewController::class, 'showPraiseRequests']);
        $group->get('/settings', [ModeratorSettingsController::class, 'showSettings']);
        $group->post('/settings', [ModeratorSettingsController::class, 'updateSettings']);
        $group->post('/prayer/approve', [ContentReviewController::class, 'approvePrayer']);
        $group->post('/prayer/deny', [ContentReviewController::class, 'denyPrayer']);
        $group->post('/praise/approve', [ContentReviewController::class, 'approvePraise']);
        $group->post('/praise/deny', [ContentReviewController::class, 'denyPraise']);   
    })->add(new AuthMiddleware());

    $app->group('/admin', function ($group)  {
        $group->get('/dashboard', [ContentReviewController::class, 'dashboard']);
        $group->get('/users', [AdminUserController::class, 'listUsers']);
        $group->get('/user/{id}/edit', [AdminUserController::class, 'showUserEditForm']);
        $group->post('/user/{id}/edit', [AdminUserController::class, 'updateUser']);
        $group->post('/user/{id}/delete', [AdminUserController::class, 'deleteUser']);
        $group->get('/user/create', [AdminUserController::class, 'showUserCreateForm']);
        $group->post('/user/create', [AdminUserController::class, 'createUser']);
        $group->get('/settings', [ContentReviewController::class, 'showSettings']);
    })->add(new AuthMiddleware());
};