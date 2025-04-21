<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/settings.php';

$app = AppFactory::create();

// Example route
$app->get('/posts', 'PrayerController:getPosts');

$app->run();
