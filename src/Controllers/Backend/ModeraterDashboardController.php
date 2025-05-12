<?php
namespace App\Controllers\Backend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Slim\Views\Twig;

/**
 * Summary of ModeraterDashboardController
 * - approves or denies prayer requests
 * - displays unapproved prayer requests
 */
class ModeraterDashboardController
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
        $stmt = $this->db->query("
            SELECT * 
            FROM prayers 
            WHERE approved = FALSE
            ORDER BY date_posted DESC
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

        return $response
                ->withHeader('Location', '/moderate/dashboard')
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

        return $response
                ->withHeader('Location', '/moderate/dashboard')
                ->withStatus(302);
    }
}
