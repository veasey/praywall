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
            AND s.setting_key = 'notify_new_prayer'
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
        $pagination = Paginator::paginate(
            $queryParams,
            defaultLimit: 20,
            allowedSorts: ['name', 'email', 'role', 'created_at']
        );

        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        // Optional filters
        if (!empty($queryParams['email'])) {
            $sql .= " AND email LIKE :email";
            $params['email'] = '%' . $queryParams['email'] . '%';
        }

        if (!empty($queryParams['role'])) {
            $sql .= " AND role = :role";
            $params['role'] = $queryParams['role'];
        }

        $sql .= " ORDER BY {$pagination['sort']} {$pagination['direction']} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination UI
        $countSql = "SELECT COUNT(*) FROM users WHERE 1=1";
        if (!empty($queryParams['email'])) {
            $countSql .= " AND email LIKE :email";
        }
        if (!empty($queryParams['role'])) {
            $countSql .= " AND role = :role";
        }

        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(":$key", $value);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        return [
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset'],
            ],
        ];
    }

}