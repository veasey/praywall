<?php
namespace App\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\PraiseReportRepository;
use App\Repositories\PrayerRepository;
use App\Repositories\UserRepository;
use App\Services\Herald;
use App\Middleware\ErrorHandlerMiddleware;
use App\Middleware\AuthMiddleware;
use Slim\Views\Twig;
use PDO;

class PraiseController
{
   
    private TWIG $view;
    private PDO $db;
    private PraiseReportRepository $praiseRepo;
    private PrayerRepository $prayerRepo;
    private UserRepository $userRepo;
    private Herald $herald;
    private ErrorHandlerMiddleware $errorHandler;
    private AuthMiddleware $authMiddleware;


    // Constructor that injects the Twig view service
    public function __construct(
        Twig $view, 
        PDO $db, 
        Herald $herald,
        ErrorHandlerMiddleware $errorHandler,
        AuthMiddleware $authMiddleware)
    {
        $this->view = $view;
        $this->db = $db;
        $this->praiseRepo = new PraiseReportRepository($db);
        $this->prayerRepo = new PrayerRepository($db);
        $this->userRepo = new UserRepository($db);
        $this->herald = $herald;
        $this->errorHandler = $errorHandler;
        $this->authMiddleware = $authMiddleware;
    }

    public function listPraiseReports(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int)($params['limit'] ?? 10)));
        $offset = ($page - 1) * $pageSize;
        $order = $params['order'] ?? 'DESC';

        $queryParams = [
            'page' => $page,
            'limit' => $pageSize,
            'order' => $order,
            'offset' => $offset,
            
        ];
        $paginatedPrayers = $this->praiseRepo->getApprovedPraiseReportsWithPrayedCountPaginated($queryParams);
        return $this->view->render($response, 'frontend/praise_reports/list.twig', $paginatedPrayers);
    }

    public function praiseReport(Request $request, Response $response, $args)
    {
        // Handle the prayer request form submission
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            // Check if user is moderator or admin
            $role = $this->authMiddleware->getUserRole();
            $approved = ($role === 'admin' || $role === 'moderator') ? true : false;

            $userId = $this->authMiddleware->getUserId();
            if (!$userId) {
                return $response
                    ->withHeader('Location', '/login')
                    ->withStatus(302);
            }

            $this->praiseRepo->insert(
                $data['title'],
                $data['body'],
                $userId,
                $data['prayer_id'] ?? null,
                $approved
            );

            // Send email notification to the admin
            $this->notifyModerators($data['title'], $data['body'], $approved);

            $message = 'Your praise report has been submitted.';
            if ($approved) {
                $message = 'Your praise report has been self approved and submitted.';
            }

            // Render a confirmation page
            return $this->view->render($response, 'frontend/praise_reports/request_success.twig', [
                'message' => $message,
                'home_url' => '/praises'
            ]);
        }

        $userId = $_SESSION['user']['id'] ?? 0;
        $userRole = $_SESSION['user']['role'] ?? 'user';
        $parentPrayers = ($userRole != 'user') ? $this->prayerRepo->getAllApproved() : $this->prayerRepo->getAllApprovedByUser($userId);
 
        return $this->view->render($response, 'frontend/praise_reports/request.twig', [
            'prayers' => $parentPrayers,
        ]);
    }

    private function notifyModerators($title, $description, $approved)
    {   
        $moderators = $this->userRepo->getModeratorsToNotifyOnNewPrayer();
        foreach ($moderators as $moderator) {
            // Send email to each moderator
            $this->herald->proclaim(
                $moderator['email'],
                'New Praise Report',
                "A new praise report has been submitted:\n\nTitle: $title\nDescription: $description\nApproved: " . ($approved ? 'Yes' : 'No')
            );
        }
    }

    public function approvePraiseReport(Request $request, Response $response, $args)
    {
        $this->praiseRepo->approve($args['id']);
        // Redirect to the prayers list
        return $response
                ->withHeader('Location', '/praises')
                ->withStatus(302);
    }

    public function deletePraiseReport(Request $request, Response $response, $args)
    {
        $this->praiseRepo->delete($args['id']);
        // Redirect to the prayers list
        return $response
                ->withHeader('Location', '/praises')
                ->withStatus(302);
    }
}
