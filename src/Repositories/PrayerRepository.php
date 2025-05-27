<?php
namespace App\Repositories;

use App\Utils\Paginator;
use PDO;

class PrayerRepository
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getApprovedPrayersWithPrayedCountPaginated(array $queryParams, int $userId): array
    {
        $pagination = Paginator::paginate(
            $queryParams,
            defaultLimit: 10,
            allowedSorts: ['created_at']
        );

        $sql = "
            SELECT 
                p.*,
                COUNT(DISTINCT up.user_id) AS prayed_count,
                EXISTS (
                    SELECT 1 
                    FROM user_prayers up2 
                    WHERE up2.prayer_id = p.id AND up2.user_id = :user_id
                ) AS has_prayed
            FROM prayers p
            LEFT JOIN user_prayers up ON p.id = up.prayer_id
            WHERE p.approved = TRUE
            GROUP BY p.id
            ORDER BY {$pagination['sort']} {$pagination['direction']}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Optional: count total approved prayers for pagination UI
        $countStmt = $this->db->query("SELECT COUNT(*) FROM prayers WHERE approved = TRUE");
        $total = (int)$countStmt->fetchColumn();

        return [
            'prayers' => $results,
            'pagination' => [
                'total' => $total,
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset'],
            ],
        ];
    }

    public function getUnapproved()
    {
        $stmt = $this->db->query("
            SELECT * 
            FROM prayers 
            WHERE approved = FALSE
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getTotalApprovedPrayersCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM prayers WHERE approved = TRUE");
        return (int) $stmt->fetchColumn();
    }

    public function insert(string $title, string $body, int $userId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO prayers (title, body, user_id) 
            VALUES (:title, :body, :user_id)
        ");
        $stmt->execute([
            ':title' => $title,
            ':body' => $body,
            ':user_id' => $userId
        ]);
    }

    public function approve(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE prayers 
            SET approved = TRUE 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function unapprove(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE prayers 
            SET approved = FALSE 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM prayers 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function togglePrayed(int $userId, int $prayerId): void
    {
        // Insert or delete from user_prayers table
        $stmt = $this->db->prepare("
            SELECT 1 FROM user_prayers 
            WHERE user_id = :user_id AND prayer_id = :prayer_id
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':prayer_id' => $prayerId
        ]);

        if ($stmt->fetch()) {
            // User already prayed — remove it
            $this->db->prepare("
                DELETE FROM user_prayers 
                WHERE user_id = :user_id AND prayer_id = :prayer_id
            ")->execute([
                ':user_id' => $userId,
                ':prayer_id' => $prayerId
            ]);
        } else {
            // Add the prayer
            $this->db->prepare("
                INSERT INTO user_prayers (user_id, prayer_id) 
                VALUES (:user_id, :prayer_id)
            ")->execute([
                ':user_id' => $userId,
                ':prayer_id' => $prayerId
            ]);
        }
    }
}
