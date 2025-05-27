<?php
namespace App\Controllers\Backend\Moderator;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Repositories\PrayerRepository;
use App\Repositories\PraiseReportRepository;
use Slim\Views\Twig;
use App\Middleware\ErrorHandlerMiddleware;

class ContentReviewController 
{
    private Twig $view;
    private PrayerRepository $prayerRepo;
    private PraiseReportRepository $praiseRepo;

    public function __construct(Twig $view, PrayerRepository $prayerRepo, PraiseReportRepository $praiseRepo)
    {
        $this->view       = $view;
        $this->prayerRepo = $prayerRepo;
        $this->praiseRepo = $praiseRepo;
    }

    public function showPrayerRequests(Request $request, Response $response, $args)
    {
        $unapproved = $this->prayerRepo->getUnapproved();
        return $this->view->render($response, 'backend/moderate/prayer_requests.twig', [
            'unapproved' => $unapproved
        ]);
    }

    public function approvePrayer(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $this->prayerRepo->approve($data['id']);
        ErrorHandlerMiddleware::addMessage('Prayer request approved.');

        return $response->withHeader('Location', '/moderate/requests/prayers')->withStatus(302);
    }

    public function unapprovePrayer(Request $request, Response $response, $args)
    {
        $this->prayerRepo->unapprove($args['id']);
        ErrorHandlerMiddleware::addMessage('Prayer request unapproved.');

        return $response->withHeader('Location', '/moderate/requests/prayers')->withStatus(302);
    }

    public function denyPrayer(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $this->prayerRepo->delete($data['id']);
        ErrorHandlerMiddleware::addMessage('Prayer removed.');

        return $response->withHeader('Location', '/moderate/requests/prayers')->withStatus(302);
    }

    public function showPraiseRequests(Request $request, Response $response, $args)
    {
        $unapproved = $this->praiseRepo->getUnapproved();
        return $this->view->render($response, 'backend/moderate/praise_requests.twig', [
            'unapproved' => $unapproved
        ]);
    }

    public function approvePraise(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $this->praiseRepo->approve($data['id']);
        ErrorHandlerMiddleware::addMessage('Praise request approved.');

        return $response->withHeader('Location', '/moderate/requests/praises')->withStatus(302);
    }

    public function unapprovePraise(Request $request, Response $response, $args)
    {
        $this->praiseRepo->unapprove($args['id']);
        ErrorHandlerMiddleware::addMessage('Praise request unapproved.');

        return $response->withHeader('Location', '/moderate/requests/praises')->withStatus(302);
    }

    public function denyPraise(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $this->praiseRepo->delete($data['id']);
        ErrorHandlerMiddleware::addMessage('Praise removed.');

        return $response->withHeader('Location', '/moderate/requests/praises')->withStatus(302);
    }
}
