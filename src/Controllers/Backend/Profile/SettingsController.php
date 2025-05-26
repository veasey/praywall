<?php
namespace App\Controllers\Backend\Profile;

use App\Repositories\UserRepository;
use App\Middleware\ErrorHandlerMiddleware;
use Slim\Views\Twig;
use PDO;

class SettingsController
{
    private TWIG $view;
    private PDO  $db;
    private UserRepository $userRepository;

    // Constructor that injects the Twig view service
    public function __construct(Twig $view, PDO $db)
    {
        $this->view = $view;
        $this->db   = $db;
        $this->userRepository = new UserRepository($db);
    }

    public function showProfileSettings($request, $response, $args)
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        $settings = [
            'name' => $_SESSION['user']['name'] ?? '',
            'email' => $_SESSION['user']['email'] ?? ''
        ];
        return $this->view->render($response, 'backend/profile/settings.twig', [
            'settings' => $settings
        ]);
    }

    public function updateProfileSettings($request, $response, $args)
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        $params = $request->getParsedBody();

        // Validate and sanitize input
        if (!isset($params['name']) || empty(trim($params['name']))) {
            ErrorHandlerMiddleware::addError("Name is required.");
            return $response->withHeader('Location', '/moderate/settings')->withStatus(302);
        }

        if (!isset($params['email']) || !filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            ErrorHandlerMiddleware::addError("Valid email is required.");
            return $response->withHeader('Location', '/moderate/settings')->withStatus(302);
        }
    }
}
