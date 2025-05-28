<?php
namespace App\Controllers\Frontend;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\PraiseReportRepository;
use App\Repositories\UserRepository;
use App\Services\Herald;
use App\Middleware\ErrorHandlerMiddleware;
use Slim\Views\Twig;
use PDO;

class PraiseController
{
   
    private TWIG $view;
    private PDO $db;
    private PraiseReportRepository $praiseReportRepository;
    private UserRepository $userRepository;
    private Herald $herald;

    // Constructor that injects the Twig view service
    public function __construct(
        Twig $view, 
        PDO $db, 
        Herald $herald)
    {
        $this->view = $view;
        $this->db = $db;
        $this->praiseReportRepository = new PraiseReportRepository($db);
        $this->userRepository = new UserRepository($db);
        $this->herald = $herald;
    }

    public function listPraiseReports(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int)($params['limit'] ?? 10)));
        $offset = ($page - 1) * $pageSize;

        $userId = $_SESSION['user']['id'] ?? 0;
        $order = in_array(strtolower($params['order'] ?? ''), ['asc', 'desc']) ? strtolower($params['order']) : 'desc';

        $queryParams = [
            'page' => $page,
            'limit' => $pageSize,
            'order' => $order,
            'offset' => $offset
        ];
        $paginatedPrayers = $this->praiseReportRepository->getApprovedPraiseReportsWithPrayedCountPaginated($queryParams, $userId);
        return $this->view->render($response, 'frontend/praise_reports/view.twig', $paginatedPrayers);
    }

    public function praiseReport(Request $request, Response $response, $args)
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

            $this->praiseReportRepository->insert(
                $data['title'],
                $data['body'],
                $_SESSION['user']['id']
            );

            // Send email notification to the admin
            $this->notifyModerators($data['title'], $data['body'], $approved);

            $message = 'Your praise report has been submitted.';
            if ($approved) {
                $message = 'Your praise report has been self approved and submitted.';
            }

            // Render a confirmation page
            return $this->view->render($response, 'frontend/praise_reports/request_success.twig', [
                'message' => 'Your prayer request has been submitted.',
                'home_url' => '/praises'
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
                'New Praise Report',
                "A new praise report has been submitted:\n\nTitle: $title\nDescription: $description\nApproved: " . ($approved ? 'Yes' : 'No')
            );
        }
    }

    public function approvePraiseReport(Request $request, Response $response, $args)
    {
        $this->praiseReportRepository->approvePraiseReportRequest($args['id']);
        // Redirect to the prayers list
        return $response
                ->withHeader('Location', '/praises')
                ->withStatus(302);
    }

    public function deletePraiseReport(Request $request, Response $response, $args)
    {
        $this->praiseReportRepository->deletePraiseReportRequest($args['id']);
        // Redirect to the prayers list
        return $response
                ->withHeader('Location', '/praises')
                ->withStatus(302);
    }
}
