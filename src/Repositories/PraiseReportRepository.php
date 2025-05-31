<?php
namespace App\Repositories;

use App\Utils\Paginator;
use PDO;

class PraiseReportRepository
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getApprovedPraiseReportsWithPrayedCountPaginated(array $queryParams): array
    {
        $pagination = Paginator::paginate(
            $queryParams,
            defaultLimit: 10,
            allowedSorts: ['created_at']
        );

        $sql = "
            SELECT p.*
            FROM praises p
            WHERE p.approved = TRUE
            GROUP BY p.id
            ORDER BY created_at {$pagination['direction']}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $queryParams['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $queryParams['offset'], PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Optional: count total approved prayers for pagination UI
        $countStmt = $this->db->query("SELECT COUNT(*) FROM prayers WHERE approved = TRUE");
        $total = (int)$countStmt->fetchColumn();

        return [
            'praises' => $results,
            'pagination' => [
                'total' => $total,
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'offset' => $pagination['offset'],
                'order' => $pagination['direction']
            ],
        ];
    }

    public function getUnapproved(): array
    {
        $stmt = $this->db->query("
            SELECT * 
            FROM praises 
            WHERE approved = FALSE
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalApprovedPraiseReportCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM praises WHERE approved = TRUE");
        return (int) $stmt->fetchColumn();
    }

    public function insert(string $title, string $body, ?int $userId, int $prayerId, int $approved): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO praises (title, body, user_id, prayer_id, approved) 
            VALUES (:title, :body, :user_id, :prayer_id, :approved)
        ");
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':body', $body);
        $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':prayer_id', $prayerId);
        $stmt->bindValue(':approved', $approved, PDO::PARAM_BOOL);
        return $stmt->execute();
    }


    public function approve(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE praises 
            SET approved = TRUE 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function unapprove(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE praises 
            SET approved = FALSE 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM praises 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    public function getPraiseWithPrayerId(int $prayerId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM praises 
            WHERE prayer_id = :prayer_id
        ");
        $stmt->bindValue(':prayer_id', $prayerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM praises 
            WHERE id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
