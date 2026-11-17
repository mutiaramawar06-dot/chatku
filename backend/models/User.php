<?php

declare(strict_types=1);

namespace Models;

use Helpers\IdGenerator;
use Lib\DB;

class User
{
    /**
     * Creates a new user with race-condition prevention (FR-25) and generated ULID (FR-20).
     *
     * @param array $data ['name', 'phone', 'password', 'photo_path']
     * @return array The created user record
     * @throws \RuntimeException on conflict or lock timeout
     */
    public static function createWithLock(array $data): array
    {
        $db = DB::getInstance();
        $lockName = 'register_user_lock';
        $lockTimeout = 5; // seconds

        // Acquire MariaDB application-level lock for concurrency safety (FR-25)
        $lockStmt = $db->prepare('SELECT GET_LOCK(:lock_name, :timeout)');
        $lockStmt->execute([':lock_name' => $lockName, ':timeout' => $lockTimeout]);
        $lockAcquired = (int) $lockStmt->fetchColumn();

        if ($lockAcquired !== 1) {
            throw new \RuntimeException('Unable to acquire registration lock. Please try again.', 409);
        }

        try {
            $db->beginTransaction();

            // Check if phone already registered inside locked critical section
            $checkStmt = $db->prepare('SELECT id FROM users WHERE phone = :phone LIMIT 1');
            $checkStmt->execute([':phone' => $data['phone']]);
            if ($checkStmt->fetch()) {
                $db->rollBack();
                throw new \RuntimeException('Phone number is already registered', 409);
            }

            // Generate unpredictable ULID (FR-20)
            $ulid = IdGenerator::generateUlid();
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $photoPath = $data['photo_path'] ?? null;

            $insertStmt = $db->prepare(
                'INSERT INTO users (id, name, phone, password_hash, photo_path)
                 VALUES (:id, :name, :phone, :password_hash, :photo_path)'
            );
            $insertStmt->execute([
                ':id' => $ulid,
                ':name' => $data['name'],
                ':phone' => $data['phone'],
                ':password_hash' => $passwordHash,
                ':photo_path' => $photoPath,
            ]);

            $db->commit();

            return [
                'id' => $ulid,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'photo_path' => $photoPath,
            ];
        } catch (\PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            // Check for duplicate key constraint violation (MariaDB error 1062)
            if ($e->getCode() === '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062)) {
                throw new \RuntimeException('Phone number is already registered', 409);
            }
            throw $e;
        } finally {
            // Release the lock
            $releaseStmt = $db->prepare('SELECT RELEASE_LOCK(:lock_name)');
            $releaseStmt->execute([':lock_name' => $lockName]);
        }
    }

    public static function findById(string $id): ?array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('SELECT id, name, phone, photo_path, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByPhone(string $phone): ?array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('SELECT id, name, phone, photo_path, created_at FROM users WHERE phone = :phone LIMIT 1');
        $stmt->execute([':phone' => $phone]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findForAuth(string $phone): ?array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('SELECT id, name, phone, password_hash, photo_path FROM users WHERE phone = :phone LIMIT 1');
        $stmt->execute([':phone' => $phone]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function deleteById(string $id): bool
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
