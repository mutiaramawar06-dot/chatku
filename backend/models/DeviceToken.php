<?php

declare(strict_types=1);

namespace Models;

use Lib\DB;

class DeviceToken
{
    public static function saveToken(string $userId, string $fcmToken): bool
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO device_tokens (user_id, fcm_token)
             VALUES (:user_id, :fcm_token)
             ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP'
        );
        return $stmt->execute([
            ':user_id' => $userId,
            ':fcm_token' => $fcmToken,
        ]);
    }

    public static function getTokensForUser(string $userId): array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('SELECT fcm_token FROM device_tokens WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function deleteToken(string $userId, string $fcmToken): bool
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('DELETE FROM device_tokens WHERE user_id = :user_id AND fcm_token = :fcm_token');
        return $stmt->execute([':user_id' => $userId, ':fcm_token' => $fcmToken]);
    }
}
