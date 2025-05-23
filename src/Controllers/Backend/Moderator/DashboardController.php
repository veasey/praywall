<?php
namespace App\Controllers\Backend\Moderator;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use PDO;
use Slim\Views\Twig;
use App\Middleware\ErrorHandlerMiddleware;

/**
 * Summary of DashboardController
 * - approves or denies prayer requests
 * - displays unapproved prayer requests
 * "And let us consider how we may spur one another on toward love and good deeds." - Hebrews 10:24
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

    public function showDashboard(Request $request, Response $response, $args)
    {
        $stmt = $this->db->query("
            SELECT * 
            FROM prayers 
            WHERE approved = FALSE
            ORDER BY created_at DESC
        ");
        $unapproved = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view->render($response, 'backend/moderate/requests.twig', [
            'unapproved' => $unapproved
        ]);
    }

    public function approvePrayer(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $stmt = $this->db->prepare("
            UPDATE prayers 
            SET approved = TRUE 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $data['id']]);

        ErrorHandlerMiddleware::addMessage('Prayer request approved.');

        return $response
                ->withHeader('Location', '/moderate/requests')
                ->withStatus(302);
    }

    public function unapprovePrayer(Request $request, Response $response, $args)
    { 
        $stmt = $this->db->prepare("
            UPDATE prayers 
            SET approved = FALSE 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $args['id']]);

        ErrorHandlerMiddleware::addMessage('Prayer request unapproved.');

        return $response
                ->withHeader('Location', '/moderate/requests')
                ->withStatus(302);
    }

    public function denyPrayer(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $stmt = $this->db->prepare("
            DELETE FROM prayers 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $data['id']]);

        ErrorHandlerMiddleware::addMessage('Prayer removed.');

        return $response
                ->withHeader('Location', '/moderate/requests')
                ->withStatus(302);
    }
}
