<?php
namespace App\Controllers\Backend\Admin;

use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UserController
{
    private Twig $view;
    private UserRepository $userRepo;

    public function __construct(Twig $view, UserRepository $userRepo)
    {
        $this->view = $view;
        $this->userRepo = $userRepo;
    }

    public function listUsers(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, min(100, (int)($params['limit'] ?? 20)));
        $sort = in_array($params['sort'] ?? '', ['name', 'email', 'role', 'created_at']) ? $params['sort'] : 'created_at';
        $direction = strtolower($params['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $role = $params['role'] ?? null;
        $shadowBanned = $params['shadow_banned'] ?? null;

        $queryParams = [
            'page' => $page,
            'limit' => $limit,
            'sort' => $sort,
            'dir' => $direction,
            
            // filters
            'role' => $role,
            'shadow_banned' => $shadowBanned
        ];
        $paginatedUsers = $this->userRepo->fetchPaginatedUsers($queryParams);
        return $this->view->render($response, 'backend/admin/users.twig', $paginatedUsers);
    }

    public function updateRole(Request $request, Response $response, $args): Response
    {
        $userId = (int)$args['id'];
        $params = $request->getParsedBody();
        $role = $params['role'] ?? 'user';

        $this->userRepo->updateRole($userId, $role);

        return $response->withHeader('Location', '/admin/users')->withStatus(302);
    }

    public function deleteUser(Request $request, Response $response, $args): Response
    {
        $userId = (int)$args['id'];

        // Perform the deletion logic here
        $this->userRepo->deleteUser($userId);

        return $response->withHeader('Location', '/admin/users')->withStatus(302);
    }

    public function showUserEditForm(Request $request, Response $response, $args): Response
    {
        $userId = (int)$args['id'];
        $user = $this->userRepo->getUserById($userId);

        if (!$user) {
            return $response->withStatus(404);
        }

        return $this->view->render($response, 'backend/admin/user_edit.twig', ['user' => $user]);
    }
}
