<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Views\Twig;

class TwigGlobalsMiddleware implements MiddlewareInterface
{
    private Twig $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Read and clear session flash errors
        $errors = $_SESSION['errors'] ?? [];
        $messages = $_SESSION['messages'] ?? [];
        $user = $_SESSION['user'] ?? null;
        unset($_SESSION['errors'], $_SESSION['messages']);

        $this->view->getEnvironment()->addGlobal('errors', $errors);
        $this->view->getEnvironment()->addGlobal('messages', $messages);
        $this->view->getEnvironment()->addGlobal('user', $user);

        return $handler->handle($request);
    }
}
