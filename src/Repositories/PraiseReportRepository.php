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

    public function getApprovedPraiseReportsWithPrayedCountPaginated(array $queryParams, int $userId): array
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
            ORDER BY {$pagination['sort']} {$pagination['direction']}
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
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

    public function getTotalApprovedPraiseReportCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM praises WHERE approved = TRUE");
        return (int) $stmt->fetchColumn();
    }

    public function insertPraiseReportRequest(string $title, string $body, int $userId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO praises (title, body, user_id) 
            VALUES (:title, :body, :user_id)
        ");
        $stmt->execute([
            ':title' => $title,
            ':body' => $body,
            ':user_id' => $userId
        ]);
    }

    public function approvePraiseReportRequest(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE praises 
            SET approved = TRUE 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    public function deletePraiseReportRequest(int $id): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM praises 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }
}
