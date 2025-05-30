<?php
namespace App\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\PrayerRepository;
use App\Repositories\PraiseReportRepository;
use App\Repositories\UserPrayerRepository;
use App\Repositories\UserRepository;
use App\Services\Herald;
use App\Middleware\ErrorHandlerMiddleware;
use App\Middleware\AuthMiddleware;
use Error;
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
    private PrayerRepository $prayerRepo;
    private PraiseReportRepository $praiseRepo;
    private UserPrayerRepository $userPrayerRepo;
    private UserRepository $userRepo;

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
        $this->prayerRepo = new PrayerRepository($db);
        $this->praiseRepo = new PraiseReportRepository($db);
        $this->userPrayerRepo = new UserPrayerRepository($db);
        $this->userRepo = new UserRepository($db);
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
        $paginatedPrayers = $this->prayerRepo->getApprovedPrayersWithPrayedCountPaginated($queryParams, $userId);
        return $this->view->render($response, 'frontend/prayers/list.twig', $paginatedPrayers);
    }

    public function viewPrayer(Request $request, Response $response, $args)
    {
        $prayerId = (int)$args['id'];
       
        // Get the prayer details
        $prayer = $this->prayerRepo->getPrayerById($prayerId);
        if (!$prayer) {
            $this->errorHandler->addError(new Error("Prayer not found"));
            return $response
                ->withHeader('Location', '/prayers')
                ->withStatus(404);
        }

        $praises = $this->praiseRepo->getPraiseWithPrayerId($prayerId);
        $userPrayers = $this->userPrayerRepo->getPrayersForPrayer($prayerId);

        return $this->view->render($response, 'frontend/prayers/view.twig', [
            'prayer' => $prayer,
            'praises' => $praises,
            'user_prayers' => $userPrayers
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

            $this->prayerRepo->insert(
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
        $moderators = $this->userRepo->getModeratorsToNotifyOnNewPrayer();
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
        $this->prayerRepo->approve($args['id']);
        // Redirect to the prayers list
        return $response
                ->withHeader('Location', '/prayers')
                ->withStatus(302);
    }

    public function deletePrayer(Request $request, Response $response, $args)
    {
        $this->prayerRepo->delete($args['id']);
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

        $this->prayerRepo->togglePrayed($userId, $prayerId);
        
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
