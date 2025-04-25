<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

/**
 * Specifically for adding session and error messages to Twig globals
 */
class TwigGlobalsMiddleware
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $env = $this->twig->getEnvironment();
        $env->addGlobal('session', $_SESSION);
        $env->addGlobal('errors', $_SESSION['errors'] ?? []);
        $env->addGlobal('messages', $_SESSION['messages'] ?? []);

        return $handler->handle($request);
    }
}
