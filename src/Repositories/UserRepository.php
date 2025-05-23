<?php
namespace App\Repositories;

use App\Utils\Paginator;
use PDO;

class UserRepository
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Only get moderators who want to be notified
     * @return array
     */
    public function getModeratorsToNotifyOnNewPrayer(): array
    {
        $stmt = $this->db->prepare("
            SELECT u.* 
            FROM users u
            INNER JOIN user_settings s ON u.id = s.user_id
            WHERE u.role = 'moderator'
            AND s.setting_key = 'email_notifications'
            AND s.setting_value = 'true'
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY role DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsers(array $filter)
    {
        $query = "SELECT COUNT(*) FROM users WHERE 1=1";
        $params = [];

        if (isset($filter['role'])) {
            $query .= " AND role = :role";
            $params[':role'] = $filter['role'];
        }

        if (isset($filter['shadow_banned'])) {
            $query .= " AND shadow_banned = :shadow_banned";
            $params[':shadow_banned'] = $filter['shadow_banned'];
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function fetchPaginatedUsers(array $queryParams): array
    {
        $filters = [
            'role' => $queryParams['role'] ?? null,
        ];

        $allUsers = $this->queryUsers($filters);
        $pagination = Paginator::paginate($queryParams, defaultLimit: 20, allowedSorts: ['name', 'email', 'role', 'created_at']);

        $paginatedUsers = $this->paginate($allUsers, $pagination);
        $usersWithSettings = $this->attachUserSettings($paginatedUsers, $queryParams['shadow_banned'] ?? null);

        return [
            'users' => $usersWithSettings,
            'filter' => [
                'role' => $filters['role'],
                'shadow_banned' => $queryParams['shadow_banned'] ?? null,
            ],
            'pagination' => [
                'total' => count($allUsers),
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset'],
            ],
        ];
    }

    private function queryUsers(array $filters): array
    {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND role = :role";
            $params['role'] = $filters['role'];
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function paginate(array $users, array $pagination): array
    {
        usort($users, function ($a, $b) use ($pagination) {
            $field = $pagination['sort'];
            $direction = $pagination['direction'] === 'desc' ? -1 : 1;
            return $direction * strcmp($a[$field], $b[$field]);
        });

        return array_slice($users, $pagination['offset'], $pagination['limit']);
    }

    private function attachUserSettings(array $users, $shadowFilter = null): array
    {
        if (empty($users)) {
            return [];
        }

        $ids = array_column($users, 'id');
        $in = str_repeat('?,', count($ids) - 1) . '?';

        $sql = "SELECT user_id, setting_value FROM user_settings WHERE setting_key = 'shadow_banned' AND user_id IN ($in)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);

        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[$row['user_id']] = (bool)$row['setting_value'];
        }

        $result = [];
        foreach ($users as $user) {
            $user['shadow_banned'] = $map[$user['id']] ?? false;

            if ($shadowFilter !== null && (int)$user['shadow_banned'] !== (int)$shadowFilter) {
                continue; // Skip if filtered out
            }

            $result[] = $user;
        }

        return $result;
    }

    public function updateRole(int $userId, string $role): void
    {
        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([':role' => $role, ':id' => $userId]);
    }

    public function deleteUser(int $userId): void
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }

    public function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createUser(array $userData): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password_hash, role, created_at) 
            VALUES (:name, :email, :password_hash, :role, NOW())
        ");
        $stmt->execute($userData);
        return (int)$this->db->lastInsertId();
    }

    public function updateUser(int $userId, array $userData): void
    {
        $setClause = [];
        foreach ($userData as $key => $value) {
            $setClause[] = "$key = :$key";
        }
        $setClause = implode(', ', $setClause);

        $stmt = $this->db->prepare("UPDATE users SET $setClause WHERE id = :id");
        $userData['id'] = $userId;
        $stmt->execute($userData);
    }

    public function getUserSettings(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM user_settings WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}