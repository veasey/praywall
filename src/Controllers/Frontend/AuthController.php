<?php

namespace App\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Slim\Views\Twig;

class AuthController
{
    private TWIG $view;
    private PDO  $db;

    // Constructor that injects the Twig view service
    public function __construct(Twig $view, PDO $db)
    {
        $this->view = $view;
        $this->db   = $db;
    }

    public function showLoginForm(Request $request, Response $response, $args)
    {
        return $this->view->render($response, 'frontend/auth/login.twig');
    }

    public function login(Request $request, Response $response, $args)
    {
        // Handle the login form submission
        $data = $request->getParsedBody();
        // Validate and authenticate user here
        // Redirect to dashboard or show error message
        return $response->withHeader('Location', '/')->withStatus(302);
    }

    public function logout(Request $request, Response $response, $args)
    {
        // Handle the logout action
        // Clear session or token here
        return $response->withHeader('Location', '/')->withStatus(302);
    }
}