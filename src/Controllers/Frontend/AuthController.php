<?php

namespace App\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Slim\Views\Twig;
use App\Middleware\ErrorHandlerMiddleware;
use Error;

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
        $data = $request->getParsedBody();

        // Replace this with real authentication
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
    
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Dummy hash to run password_verify regardless of user presence (prevents timing attacks)
        $dummyHash = '$2y$10$ForGodSoLovedTheWorldHeGaveHisOnlySonInEternity1n7hnbRJHxXVLeakoG8K30oukPsA.ztMG'; // a valid bcrypt hash
        $hash = $user['password_hash'] ?? $dummyHash;
        $passwordVerified = password_verify($password, $hash);

        if ($user && $passwordVerified) {
            // Set session or token here
            $_SESSION['user'] = $user;
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        // failed login
        ErrorHandlerMiddleware::addError('Invalid login credentials.');
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function logout(Request $request, Response $response, $args)
    {
        $_SESSION['user'] = null;
        return $response->withHeader('Location', '/')->withStatus(302);
    }
}