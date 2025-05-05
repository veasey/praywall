<?php

namespace App\Controllers\Frontend;

use App\Validation\RegisterValidator;
use App\Validation\LoginValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Slim\Views\Twig;
use App\Middleware\ErrorHandlerMiddleware;

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

    public function showRegisterForm(Request $request, Response $response, $args)
    {
        return $this->view->render($response, 'frontend/auth/register.twig');
    }

    public function login(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $errors = LoginValidator::validate($data);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                ErrorHandlerMiddleware::addError($error);
            }
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
    
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Dummy hash to run password_verify regardless of user presence (prevents timing attacks)
        $dummyHash = '$2y$10$ForGodSoLovedTheWorldHeGaveHisOnlySonInEternity1n7hnbRJHxXVLeakoG8K30oukPsA.ztMG'; // a valid bcrypt hash
        $hash = $user['password_hash'] ?? $dummyHash;
        $passwordVerified = password_verify($password, $hash);

        if ($user && $passwordVerified) {
            // Set session or token here
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role']
            ];
            $_SESSION['messages'] = ['Login successful!'];
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

    public function register(Request $request, Response $response): Response {
        $data = $request->getParsedBody();

        $errors = RegisterValidator::validate($data);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                ErrorHandlerMiddleware::addError($error);
            }
            return $response->withHeader('Location', '/register')->withStatus(302);
        }

        // hash password & insert user
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['name'], $data['email'], $hash, 'user']);

        ErrorHandlerMiddleware::addMessage('Registration successful. You may now log in.');
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}