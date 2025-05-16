<?php
namespace App\Controllers\Frontend;

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
        $stmt = $this->db->query("
            SELECT * 
            FROM prayers 
            WHERE approved = TRUE
            ORDER BY date_posted DESC
        ");
        $prayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->view->render($response, 'frontend/prayers/view.twig', [
            'prayers' => $prayers
        ]);
    }

    public function prayerRequest(Request $request, Response $response, $args)
    {
        // Handle the prayer request form submission
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            // Default: not approved
            $approved = false;

            // Check if user is moderator or admin
            if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])) {
                $role = $_SESSION['user']['role'];
                if ($role === 'admin' || $role === 'moderator') {
                    $approved = true;
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO prayers (title, body, date_posted, approved) 
                VALUES (:title, :body, NOW(), :approved)
            ");
            $stmt->execute([
                ':title'    => $data['title'],
                ':body'     => $data['body'],
                ':approved' => $approved ? 1 : 0
            ]);

            $message = 'Your prayer request has been submitted.';
            if ($approved) {
                $message = 'Your prayer request has been self approved and submitted.';
            }

            // Render a confirmation page
            return $this->view->render($response, 'frontend/prayers/request_success.twig', [
                'message' => 'Your prayer request has been submitted.',
                'home_url' => '/prayers'
            ]);
        }

        return $this->view->render($response, 'frontend/prayers/request.twig');
    }
    public function approvePrayer(Request $request, Response $response, $args)
    {
        // Handle the prayer approval
        $stmt = $this->db->prepare("
            UPDATE prayers 
            SET approved = TRUE 
            WHERE id = :id
        ");
        $stmt->execute([
            ':id' => $args['id']
        ]);

        return $response
                ->withHeader('Location', '/prayers')
                ->withStatus(302);
    }

    public function deletePrayer(Request $request, Response $response, $args)
    {
        // Handle the prayer deletion
        $stmt = $this->db->prepare("
            DELETE FROM prayers 
            WHERE id = :id
        ");
        $stmt->execute([
            ':id' => $args['id']
        ]);

        return $response
                ->withHeader('Location', '/prayers')
                ->withStatus(302);
    }
}
