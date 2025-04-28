<?php
// src/errorHandler.php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// 404 Error Handler
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (Request $request, Throwable $exception, bool $displayErrorDetails) use ($app): Response {
        $response = $app->getResponseFactory()->createResponse();
        $view = $app->getContainer()->get('view');
        return $view->render($response->withStatus(404), 'errors/404.twig');
    }
);
