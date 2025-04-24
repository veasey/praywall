<?php

use App\Controllers\ModerateController;
use Slim\App;

return function (App $app) {

    $controller = $app->getContainer()->get(ModerateController::class);

    // routes here ...
    // $app->get('/', [$controller, 'listUnapprovedPrayers']);
};