<?php

namespace App\Repositories;

use PDO;

class UserSettingsRepository
{
    public function __construct(private PDO $db) {}

    public function getSetting(int $userId, string $key): ?string
    {
        $stmt = $this->db->prepare('SELECT setting_value FROM user_settings WHERE user_id = :user_id AND setting_key = :key');
        $stmt->execute(['user_id' => $userId, 'key' => $key]);
        return $stmt->fetchColumn() ?: null;
    }

    public function setSetting(int $userId, string $key, string $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO user_settings (user_id, setting_key, setting_value)
             VALUES (:user_id, :key, :value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        $stmt->execute(['user_id' => $userId, 'key' => $key, 'value' => $value]);
    }

    public function deleteSetting(int $userId, string $key): void
    {
        $stmt = $this->db->prepare('DELETE FROM user_settings WHERE user_id = :user_id AND setting_key = :key');
        $stmt->execute(['user_id' => $userId, 'key' => $key]);
    }
}
