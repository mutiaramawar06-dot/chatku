<?php

declare(strict_types=1);

namespace Models;

use Lib\DB;

class Contact
{
    /**
     * List all contacts belonging to the owner, including the target user's details.
     */
    public static function getAllByOwner(string $ownerId): array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'SELECT c.id, c.alias_name, c.created_at,
                    u.id AS contact_user_id, u.name AS user_name, u.phone AS user_phone, u.photo_path
             FROM contacts c
             INNER JOIN users u ON c.contact_user_id = u.id
             WHERE c.owner_id = :owner_id
             ORDER BY c.alias_name ASC'
        );
        $stmt->execute([':owner_id' => $ownerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get specific contact by ID and owner.
     */
    public static function findByIdAndOwner(int $contactId, string $ownerId): ?array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'SELECT c.id, c.alias_name, c.created_at,
                    u.id AS contact_user_id, u.name AS user_name, u.phone AS user_phone, u.photo_path
             FROM contacts c
             INNER JOIN users u ON c.contact_user_id = u.id
             WHERE c.id = :id AND c.owner_id = :owner_id
             LIMIT 1'
        );
        $stmt->execute([':id' => $contactId, ':owner_id' => $ownerId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Find contact by owner and contact_user_id.
     */
    public static function findByOwnerAndContactUser(string $ownerId, string $contactUserId): ?array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'SELECT id, alias_name FROM contacts WHERE owner_id = :owner_id AND contact_user_id = :contact_user_id LIMIT 1'
        );
        $stmt->execute([':owner_id' => $ownerId, ':contact_user_id' => $contactUserId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Creates a new contact.
     */
    public static function create(string $ownerId, string $contactUserId, string $aliasName): int
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO contacts (owner_id, contact_user_id, alias_name)
             VALUES (:owner_id, :contact_user_id, :alias_name)'
        );
        $stmt->execute([
            ':owner_id' => $ownerId,
            ':contact_user_id' => $contactUserId,
            ':alias_name' => $aliasName,
        ]);
        return (int) $db->lastInsertId();
    }

    /**
     * Updates an existing contact.
     */
    public static function update(int $contactId, string $ownerId, string $aliasName, ?string $newContactUserId = null): bool
    {
        $db = DB::getInstance();
        if ($newContactUserId !== null) {
            $stmt = $db->prepare(
                'UPDATE contacts
                 SET alias_name = :alias_name, contact_user_id = :contact_user_id
                 WHERE id = :id AND owner_id = :owner_id'
            );
            return $stmt->execute([
                ':alias_name' => $aliasName,
                ':contact_user_id' => $newContactUserId,
                ':id' => $contactId,
                ':owner_id' => $ownerId,
            ]);
        }

        $stmt = $db->prepare(
            'UPDATE contacts SET alias_name = :alias_name WHERE id = :id AND owner_id = :owner_id'
        );
        return $stmt->execute([
            ':alias_name' => $aliasName,
            ':id' => $contactId,
            ':owner_id' => $ownerId,
        ]);
    }

    /**
     * Deletes a contact.
     */
    public static function delete(int $contactId, string $ownerId): bool
    {
        $db = DB::getInstance();
        $stmt = $db->prepare('DELETE FROM contacts WHERE id = :id AND owner_id = :owner_id');
        $stmt->execute([':id' => $contactId, ':owner_id' => $ownerId]);
        return $stmt->rowCount() > 0;
    }
}
