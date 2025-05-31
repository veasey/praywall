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

    public function getApprovedPrayersWithPrayedCountPaginated(array $queryParams, ?int $userId): array
    {
        $pagination = Paginator::paginate(
            $queryParams,
            defaultLimit: 10,
            allowedSorts: ['created_at']
        );

        $existsQuery = $this->getExistsQuery($userId);
        $sql = "
            SELECT 
                p.*,
                COUNT(DISTINCT up.user_id) AS prayed_count,
                $existsQuery
            FROM prayers p
            LEFT JOIN user_prayers up ON p.id = up.prayer_id
            WHERE p.approved = TRUE
            GROUP BY p.id
            ORDER BY {$pagination['sort']} {$pagination['direction']}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        if ($userId) {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $queryParams['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $queryParams['offset'], PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $prayersWithInlinePraises = $this->attachInlinePrayerContent($results);

        // Optional: count total approved prayers for pagination UI
        $countStmt = $this->db->query("SELECT COUNT(*) FROM prayers WHERE approved = TRUE");
        $total = (int)$countStmt->fetchColumn();

        return [
            'prayers' => $prayersWithInlinePraises,
            'pagination' => [
                'total' => $total,
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset'],
                'order' => $pagination['direction']
            ],
        ];
    }

    public function getPrayerById(int $id, ?int $userId): ?array
    {
        $existsQuery = $this->getExistsQuery($userId);
        $stmt = $this->db->prepare("
            SELECT 
                p.*,
                COUNT(DISTINCT up.user_id) AS prayed_count,
                $existsQuery
            FROM prayers p
            LEFT JOIN user_prayers up ON p.id = up.prayer_id
            WHERE p.id = :id
            GROUP BY p.id
        ");
        if ($userId) {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        // Fetch the prayer and its prayed count
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getExistsQuery(?int $userId): string
    {
        if ($userId) {
            return "
                EXISTS (
                    SELECT 1 
                    FROM user_prayers up 
                    WHERE up.prayer_id = p.id AND up.user_id = :user_id
                ) AS has_prayed
            ";
        }
        return "FALSE AS has_prayed";
    }  

    private function attachInlinePrayerContent(array $prayers): array
    {
        if (empty($prayers)) return $prayers;

        $ids = array_column($prayers, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            SELECT 
                id, 
                prayer_id, 
                title, 
                body 
            FROM praises 
            WHERE prayer_id IN ($placeholders)
            AND approved = TRUE
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);

        $praises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group praises by prayer_id
        $praisesByPrayerId = [];
        foreach ($praises as $praise) {
            $praisesByPrayerId[$praise['prayer_id']][] = $praise;
        }

        foreach ($prayers as &$prayer) {
            $prayer['praises'] = $praisesByPrayerId[$prayer['id']] ?? [];
        }

        return $prayers;
    }

    public function getAllApproved(): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*
            FROM prayers p
            WHERE p.approved = TRUE
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllApprovedByUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT p.*
            FROM prayers p
            WHERE p.approved = TRUE AND p.user_id = :user_id
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function insert(string $title, string $body, int $userId, int $approved): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO prayers (title, body, user_id, approved) 
            VALUES (:title, :body, :user_id, :approved)
        ");
        $stmt->execute([
            ':title' => $title,
            ':body' => $body,
            ':user_id' => $userId,
            ':approved' => $approved ? 1 : 0
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

    public function togglePrayed(int $userId, int $prayerId): bool
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
            return $this->db->prepare("
                DELETE FROM user_prayers 
                WHERE user_id = :user_id AND prayer_id = :prayer_id
            ")->execute([
                ':user_id' => $userId,
                ':prayer_id' => $prayerId
            ]);
        } else {
            // Add the prayer
            return $this->db->prepare("
                INSERT INTO user_prayers (user_id, prayer_id) 
                VALUES (:user_id, :prayer_id)
            ")->execute([
                ':user_id' => $userId,
                ':prayer_id' => $prayerId
            ]);
        }
    } 
}
