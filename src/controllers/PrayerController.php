<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
use Slim\Views\Twig;

class PrayerController
{

    /*      _
          _|_|_
        ,|_| |_|_
        || | | |_|
        || | | | |
        || | | | |
       _|| | | | |
     ||)\  ^ ^ ^ |
     || |        |
     || |        |
     || |        |
     \\          |
      \\         /
       )\       (
      /  \       \
     /    \       \
           \       \*/

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
        // Example query — replace with actual DB logic
        $stmt = $this->db->query("SELECT * FROM prayers ORDER BY date_posted DESC");
        $prayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Render the template using the injected Twig view
        return $this->view->render($response, 'prayers.twig', [
            'prayers' => $prayers
        ]);
    }
}
