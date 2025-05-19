<?php
namespace App\Repositories;

use PDO;

class PrayerRepository
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getApprovedPrayersWithPrayedCount(int $userId, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare("
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
            ORDER BY p.date_posted DESC
            LIMIT :limit OFFSET :offset            
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalApprovedPrayersCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM prayers WHERE approved = TRUE");
        return (int) $stmt->fetchColumn();
    }

    public function insertPrayerRequest(string $title, string $description, int $userId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO prayers (title, description, user_id) 
            VALUES (:title, :description, :user_id)
        ");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':user_id' => $userId
        ]);
    }

    public function approvePrayerRequest(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE prayers 
            SET approved = TRUE 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
    }

    public function deletePrayerRequest(int $id): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM prayers 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
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
