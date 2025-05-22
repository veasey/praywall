<?php
namespace App\Controllers\Backend\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Slim\Views\Twig;

/**
 * Summary of AdminDashboardController
 * - manage users and permissions
 * - manage churches and locations
 */
class DashboardController
{
    private TWIG $view;
    private PDO  $db;

    // Constructor that injects the Twig view service
    public function __construct(Twig $view, PDO $db)
    {
        $this->view = $view;
        $this->db   = $db;
    }

    public function dashboard(Request $request, Response $response, $args)
    {
        return $this->view->render($response, 'backend/admin/dashboard.twig');
    }
}
