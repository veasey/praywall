<?php
namespace App\Repositories;

use App\Utils\Paginator;
use PDO;

class UserPrayerRepository
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getPrayersForPrayer(int $prayerId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id, 
                name
            FROM user_prayers
            LEFT JOIN users ON user_prayers.user_id = users.id
            WHERE user_prayers.prayer_id = :prayerId
        ");
        $stmt->bindValue(':prayerId', $prayerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
