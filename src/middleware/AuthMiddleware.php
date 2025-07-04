<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $_SESSION ?? [];

        if (!isset($session['user'])) {
            $response = new Response();
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        return $handler->handle($request);
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user']);
    }

    public function getUserId(): ?int
    {
        return $this->isAuthenticated() ? (int)$_SESSION['user']['id'] : null;
    }

    public function getUserRole(): ?string
    {
        return $this->isAuthenticated() ? $_SESSION['user']['role'] : null;
    }
}
