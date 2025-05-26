<?php
namespace App\Controllers\Backend\Moderator;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use PDO;
use Slim\Views\Twig;
use App\Middleware\ErrorHandlerMiddleware;
use App\Repositories\UserSettingsRepository;

/**
 * Summary of Moderator Settings Controller
 * - approves or denies prayer requests
 * - displays unapproved prayer requests
 * "And let us consider how we may spur one another on toward love and good deeds." - Hebrews 10:24
 */
class SettingsController
{
    private TWIG $view;
    private PDO  $db;
    private UserSettingsRepository $userSettingsRepository;

    // Constructor that injects the Twig view service
    public function __construct(Twig $view, PDO $db)
    {
        $this->view = $view;
        $this->db   = $db;
        $this->userSettingsRepository = new UserSettingsRepository($db);
    }

    public function showSettings(Request $request, Response $response, $args)
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        $settings = [
            'email_notifications' => $this->userSettingsRepository->getSetting($userId, 'email_notifications'),
            'automatic_approval' => $this->userSettingsRepository->getSetting($userId, 'automatic_approval')
        ];
        return $this->view->render($response, 'backend/moderate/settings.twig', $settings);
    }

    public function updateSettings(Request $request, Response $response, $args)
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        $params = $request->getParsedBody() ?? [];

        // Validate and sanitize input
        foreach(['email_notifications', 'automatic_approval'] as $settingFieldName) {
            $this->userSettingsRepository->setSetting($userId, $settingFieldName, !empty($params[$settingFieldName]) ? 'true' : 'false');
        }

        // Add success message
        ErrorHandlerMiddleware::addSuccess("Settings updated successfully.");

        // Redirect to the settings page
        return $response->withHeader('Location', '/moderate/settings')->withStatus(302);
    }
}