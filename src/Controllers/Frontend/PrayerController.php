<?php
namespace App\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\PrayerRepository;
use App\Repositories\UserRepository;
use App\Services\Herald;
use App\Middleware\ErrorHandlerMiddleware;
use App\Middleware\AuthMiddleware;
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
    private PDO $db;
    private PrayerRepository $prayerRepository;
    private UserRepository $userRepository;

    private Herald $herald;
    private ErrorHandlerMiddleware $errorHandler;
    private AuthMiddleware $authMiddleware;

    // Constructor that injects the Twig view service
    public function __construct(
        Twig $view, 
        PDO $db, 
        Herald $herald)
    {
        $this->view = $view;
        $this->db = $db;
        $this->prayerRepository = new PrayerRepository($db);
        $this->userRepository = new UserRepository($db);
        $this->herald = $herald;
        $this->errorHandler = new ErrorHandlerMiddleware();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function listPrayers(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int)($params['limit'] ?? 10)));
        $offset = ($page - 1) * $pageSize;
        $order = strtoupper($params['order'] ?? 'DESC');

        // Get user ID from session
        $userId = $this->authMiddleware->getUserId();
        if (!$userId) {
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }
    
        $queryParams = [
            'page' => $page,
            'limit' => $pageSize,
            'order' => $order,
            'offset' => $offset
        ];
        $paginatedPrayers = $this->prayerRepository->getApprovedPrayersWithPrayedCountPaginated($queryParams, $userId);
        return $this->view->render($response, 'frontend/prayers/view.twig', $paginatedPrayers);
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

            $this->prayerRepository->insert(
                $data['title'],
                $data['body'],
                $_SESSION['user']['id'],
                $approved ? 1 : 0 // Set user_id to null if approved, otherwise 0
            );

            // Send email notification to the admin
            $this->notifyModerators($data['title'], $data['body'], $approved);

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

    private function notifyModerators($title, $description, $approved)
    {   
        $moderators = $this->userRepository->getModeratorsToNotifyOnNewPrayer();
        foreach ($moderators as $moderator) {
            // Send email to each moderator
            $this->herald->proclaim(
                $moderator['email'],
                'New Prayer Request',
                "A new prayer request has been submitted:\n\nTitle: $title\nDescription: $description\nApproved: " . ($approved ? 'Yes' : 'No')
            );
        }
    }

    public function approvePrayer(Request $request, Response $response, $args)
    {
        $this->prayerRepository->approve($args['id']);
        // Redirect to the prayers list
        return $response
                ->withHeader('Location', '/prayers')
                ->withStatus(302);
    }

    public function deletePrayer(Request $request, Response $response, $args)
    {
        $this->prayerRepository->delete($args['id']);
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
        $pageSize = max(1, min(100, (int)($request->getQueryParams()['limit'] ?? 10)));

        $this->prayerRepository->togglePrayed($userId, $prayerId);
        
        // get redirect query param
        $queryParams = http_build_query([
            'redirect' => $request->getQueryParams()['redirect'] ?? 1,
            'limit'    => $pageSize,
            'page'     => $request->getQueryParams()['page'] ?? 1,
            'order'    => $request->getQueryParams()['order'] ?? 'DESC'
        ]);

        // redirect back to prayers page with anchor and page param
        return $response
            ->withHeader('Location', "/prayers?" . $queryParams . "#prayer-$prayerId")
            ->withStatus(302);
    }
}
