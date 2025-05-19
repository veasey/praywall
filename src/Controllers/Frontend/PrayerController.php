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
        $userId = $_SESSION['user']['id'] ?? null;

        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                COUNT(DISTINCT up.user_id) AS prayed_count,
                EXISTS (
                    SELECT 1 
                    FROM user_prayers up2 
                    WHERE up2.prayer_id = p.id AND up2.user_id = :user_id
                ) AS has_prayed
            FROM prayers p
            LEFT JOIN user_prayers up ON p.id = up.prayer_id
            WHERE p.approved = TRUE
            GROUP BY p.id
            ORDER BY p.date_posted DESC
        ");

        $stmt->execute(['user_id' => $userId]);
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

    public function pray(Request $request, Response $response, $args)
    {
        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        $userId = $user['id'];
        $prayerId = $args['id'];

        // Check if user already prayed
        $stmt = $this->db->prepare("
            SELECT 1 FROM user_prayers 
            WHERE user_id = :user_id AND prayer_id = :prayer_id
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':prayer_id' => $prayerId
        ]);

        if ($stmt->fetch()) {
            // User already prayed — remove it
            $this->db->prepare("
                DELETE FROM user_prayers 
                WHERE user_id = :user_id AND prayer_id = :prayer_id
            ")->execute([
                ':user_id' => $userId,
                ':prayer_id' => $prayerId
            ]);
        } else {
            // Add the prayer
            $this->db->prepare("
                INSERT INTO user_prayers (user_id, prayer_id) 
                VALUES (:user_id, :prayer_id)
            ")->execute([
                ':user_id' => $userId,
                ':prayer_id' => $prayerId
            ]);
        }

        return $response
            ->withHeader('Location', '/prayers')
            ->withStatus(302);
    }
}
