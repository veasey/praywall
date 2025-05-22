<?php
namespace App\Controllers\Backend\Admin;

use App\Repositories\UserRepository;
use App\Repositories\UserSettingsRepository;
use App\Middleware\ErrorHandlerMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UserController
{
    private Twig $view;
    private UserRepository $userRepo;
    private UserSettingsRepository $userSettingsRepo;

    public function __construct(Twig $view, UserRepository $userRepo, UserSettingsRepository $userSettingsRepo)
    {
        $this->view = $view;
        $this->userRepo = $userRepo;
        $this->userSettingsRepo = $userSettingsRepo;
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
        $userSettings = $this->userSettingsRepo->getAllSettings($userId);

        if (!$user) {
            return $response->withStatus(404);
        }

        return $this->view->render($response, 'backend/admin/user_edit.twig', [
            'user' => $user,
            'user_settings' => $userSettings,
        ]);
    }

    public function updateUser(Request $request, Response $response, $args): Response
    {
        $userId = (int)$args['id'];
        $params = $request->getParsedBody();

        $userData = [
            'name' => $params['name'] ?? '',
            'email' => $params['email'] ?? '',
            'role' => $params['role'] ?? 'user',
        ];
        $this->userRepo->updateUser($userId, $userData);

        $settings = $params['settings'] ?? [];
        foreach ($settings as $key => $value) {
            $this->userSettingsRepo->setSetting($userId, $key, $value);
        }

        ErrorHandlerMiddleware::addSuccess('User updated successfully.');
        return $response->withHeader('Location', '/admin/users')->withStatus(302);
    }

    public function showUserCreateForm(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'backend/admin/user_create.twig');
    }

    public function createUser(Request $request, Response $response): Response
    {
        $params = $request->getParsedBody();
        $name = $params['name'] ?? '';
        $email = $params['email'] ?? '';
        $password = $params['password'] ?? '';
        $role = $params['role'] ?? 'user';

        // Perform the user creation logic here
        $this->userRepo->createUser($name, $email, $password, $role);

        return $response->withHeader('Location', '/admin/users')->withStatus(302);
    }
}
