<?php
namespace App\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\PrayerRepository;
use Slim\Views\Twig;
use PDO;

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
    private PrayerRepository $prayerRepository;

    // Constructor that injects the Twig view service
    public function __construct(Twig $view, PDO $db)
    {
        $this->view = $view;
        $this->prayerRepository = new PrayerRepository($db);
    }

    public function listPrayers(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;

        $userId = $_SESSION['user']['id'] ?? 0;

        $totalPrayers = $this->prayerRepository->getTotalApprovedPrayersCount();
        $prayers = $this->prayerRepository->getApprovedPrayersWithPrayedCount($userId, $pageSize, $offset);
        $totalPages = (int) ceil($totalPrayers / $pageSize);

        return $this->view->render($response, 'frontend/prayers/view.twig', [
            'prayers' => $prayers,
            'currentPage' => $page,
            'totalPages' => $totalPages,
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

            $this->prayerRepository->insertPrayerRequest(
                $data['title'],
                $data['description'],
                $_SESSION['user']['id']
            );

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
        $this->prayerRepository->approvePrayerRequest($args['id']);
        // Redirect to the prayers list
        return $response
                ->withHeader('Location', '/prayers')
                ->withStatus(302);
    }

    public function deletePrayer(Request $request, Response $response, $args)
    {
        $this->prayerRepository->deletePrayerRequest($args['id']);
        // Redirect to the prayers list
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

        $this->prayerRepository->togglePrayed($userId, $prayerId);

        return $response
            ->withHeader('Location', '/prayers')
            ->withStatus(302);
    }
}
