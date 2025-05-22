<?php
namespace App\Controllers\Backend\Moderator;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use PDO;
use Slim\Views\Twig;
use App\Middleware\ErrorHandlerMiddleware;
use App\Repositories\UserSettingsRepository;

/**
 * Summary of ModeraterDashboardController
 * - approves or denies prayer requests
 * - displays unapproved prayer requests
 * "And let us consider how we may spur one another on toward love and good deeds." - Hebrews 10:24
 */
class ModeratorSettingsController
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
        return $this->view->render($response, 'backend/moderate/settings.twig', [
            'settings' => $settings
        ]);
    }

    public function updateSettings(Request $request, Response $response, $args)
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        $params = $request->getParsedBody();

        // Check if fields are present, default to false if not set
        $emailNotifications = isset($params['notify_email']) && filter_var($params['notify_email'], FILTER_VALIDATE_BOOLEAN);
        $automaticApproval = isset($params['auto_approve']) && filter_var($params['auto_approve'], FILTER_VALIDATE_BOOLEAN);

        $this->userSettingsRepository->setSetting($userId, 'email_notifications', $emailNotifications ? 'true' : 'false');
        $this->userSettingsRepository->setSetting($userId, 'automatic_approval', $automaticApproval ? 'true' : 'false');

        // Add success message
        ErrorHandlerMiddleware::addSuccess("Settings updated successfully.");

        // Redirect to the settings page
        return $response->withHeader('Location', '/moderate/settings')->withStatus(302);
    }
}