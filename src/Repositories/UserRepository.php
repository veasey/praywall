<?php
namespace App\Repositories;

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
}