<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Slim\Views\Twig;

class ModerateController
{
    private TWIG $view;
    private PDO  $db;

    // Constructor that injects the Twig view service
    public function __construct(Twig $view, PDO $db)
    {
        $this->view = $view;
        $this->db   = $db;
    }

    public function listPrayers(Request $request, Response $response, $args)
    {
        $stmt = $this->db->query("
            SELECT * 
            FROM prayers 
            WHERE approved = FALSE
            ORDER BY date_posted DESC
        ");
        $unapproved = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view->render($response, 'moderate.twig', [
            'unapproved' => $unapproved
        ]);
    }
}
