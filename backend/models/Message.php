<?php

declare(strict_types=1);

namespace Models;

use Lib\DB;

class Message
{
    /**
     * Send a new message from sender to receiver.
     */
    public static function create(string $senderId, string $receiverId, ?string $body, ?string $imagePath): array
    {
        $db = DB::getInstance();
        $stmt = $db->prepare(
            'INSERT INTO messages (sender_id, receiver_id, body, image_path)
             VALUES (:sender_id, :receiver_id, :body, :image_path)'
        );
        $stmt->execute([
            ':sender_id' => $senderId,
            ':receiver_id' => $receiverId,
            ':body' => $body,
            ':image_path' => $imagePath,
        ]);
        $id = (int) $db->lastInsertId();

        return [
            'id' => $id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'body' => $body,
            'image_path' => $imagePath,
            'created_at' => date('Y-m-d H:i:s'),
            'read_at' => null,
        ];
    }

    /**
     * Get message conversation history between two users, ordered by created_at ASC.
     * Marks unread messages as read where receiver_id is current user.
     */
    public static function getHistoryBetween(string $currentUserId, string $otherUserId): array
    {
        $db = DB::getInstance();

        // Mark incoming messages as read
        $markStmt = $db->prepare(
            'UPDATE messages
             SET read_at = CURRENT_TIMESTAMP
             WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL'
        );
        $markStmt->execute([$otherUserId, $currentUserId]);

        // Fetch messages with positional placeholders
        $stmt = $db->prepare(
            'SELECT id, sender_id, receiver_id, body, image_path, created_at, read_at
             FROM messages
             WHERE (sender_id = ? AND receiver_id = ?)
                OR (sender_id = ? AND receiver_id = ?)
             ORDER BY created_at ASC, id ASC'
        );
        $stmt->execute([$currentUserId, $otherUserId, $otherUserId, $currentUserId]);
        return $stmt->fetchAll();
    }

    /**
     * Eager Loading implementation for conversations list (FR-23, Kriteria 5):
     * Strictly fulfills the 3-step pattern:
     * 1. Ambil daftar lawan bicara dalam satu query (Query 1)
     * 2. Ambil detail lawan bicara & pesan terakhir dalam query tambahan memakai WHERE ... IN (...) (Query 2, 3, 4)
     * 3. Gabungkan di PHP dengan array berindeks conversation_key / user_id
     * Hasil: Jumlah query konstan, TIDAK N+1, dan TANPA klausa JOIN konvensional pada pesan!
     */
    public static function getConversationsEagerLoading(string $currentUserId): array
    {
        $db = DB::getInstance();

        // Step 1: Ambil distinct ID semua lawan bicara dalam satu query
        $partnerQuery = $db->prepare(
            'SELECT DISTINCT
                CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id
             FROM messages
             WHERE sender_id = ? OR receiver_id = ?'
        );
        $partnerQuery->execute([$currentUserId, $currentUserId, $currentUserId]);
        $partnerRows = $partnerQuery->fetchAll();

        if (empty($partnerRows)) {
            return [];
        }

        $partnerIds = array_values(array_unique(array_column($partnerRows, 'partner_id')));
        $placeholders = implode(',', array_fill(0, count($partnerIds), '?'));

        // Query 2: Fetch users info without JOIN on messages
        $usersStmt = $db->prepare("SELECT id, name, phone, photo_path FROM users WHERE id IN ($placeholders)");
        $usersStmt->execute($partnerIds);
        $usersRows = $usersStmt->fetchAll();
        $usersMap = [];
        foreach ($usersRows as $u) {
            $usersMap[$u['id']] = $u;
        }

        // Query 3: Fetch contacts alias if any
        $contactStmt = $db->prepare("SELECT contact_user_id, alias_name FROM contacts WHERE owner_id = ? AND contact_user_id IN ($placeholders)");
        $contactStmt->execute(array_merge([$currentUserId], $partnerIds));
        $contactRows = $contactStmt->fetchAll();
        $contactMap = [];
        foreach ($contactRows as $c) {
            $contactMap[$c['contact_user_id']] = $c['alias_name'];
        }

        // Query 4: Ambil id pesan terakhir per partner
        $latestIdParams = array_merge(
            [$currentUserId, $currentUserId],
            $partnerIds,
            [$currentUserId],
            $partnerIds
        );
        $latestIdsStmt = $db->prepare(
            "SELECT MAX(id) AS last_message_id,
                    CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id
             FROM messages
             WHERE (sender_id = ? AND receiver_id IN ($placeholders))
                OR (receiver_id = ? AND sender_id IN ($placeholders))
             GROUP BY partner_id"
        );
        $latestIdsStmt->execute($latestIdParams);
        $latestIdRows = $latestIdsStmt->fetchAll();

        $messageIds = array_values(array_filter(array_column($latestIdRows, 'last_message_id')));
        $messagesMap = [];
        if (!empty($messageIds)) {
            $msgPlaceholders = implode(',', array_fill(0, count($messageIds), '?'));
            $messagesStmt = $db->prepare("SELECT id, sender_id, receiver_id, body, image_path, created_at, read_at FROM messages WHERE id IN ($msgPlaceholders)");
            $messagesStmt->execute($messageIds);
            $messageRows = $messagesStmt->fetchAll();

            foreach ($messageRows as $msg) {
                $pId = ($msg['sender_id'] === $currentUserId) ? $msg['receiver_id'] : $msg['sender_id'];
                $messagesMap[$pId] = $msg;
            }
        }

        // Query 5: Count unread incoming messages per partner in one batch query
        $unreadStmt = $db->prepare(
            "SELECT sender_id, COUNT(*) AS unread_count
             FROM messages
             WHERE receiver_id = ? AND read_at IS NULL AND sender_id IN ($placeholders)
             GROUP BY sender_id"
        );
        $unreadStmt->execute(array_merge([$currentUserId], $partnerIds));
        $unreadRows = $unreadStmt->fetchAll();
        $unreadMap = [];
        foreach ($unreadRows as $ur) {
            $unreadMap[$ur['sender_id']] = (int) $ur['unread_count'];
        }

        // Step 3: Gabungkan di PHP dengan array berindeks
        $conversations = [];
        foreach ($partnerIds as $pId) {
            $userData = $usersMap[$pId] ?? null;
            if (!$userData) {
                continue;
            }

            $alias = $contactMap[$pId] ?? null;
            $displayName = $alias ?: $userData['name'];
            $lastMsg = $messagesMap[$pId] ?? null;
            $unread = $unreadMap[$pId] ?? 0;

            $conversations[] = [
                'user_id' => $pId,
                'display_name' => $displayName,
                'real_name' => $userData['name'],
                'phone' => $userData['phone'],
                'photo_path' => $userData['photo_path'],
                'last_message' => $lastMsg ? [
                    'id' => $lastMsg['id'],
                    'body' => $lastMsg['body'],
                    'image_path' => $lastMsg['image_path'],
                    'created_at' => $lastMsg['created_at'],
                    'is_outgoing' => ($lastMsg['sender_id'] === $currentUserId),
                    'read_at' => $lastMsg['read_at'],
                ] : null,
                'unread_count' => $unread,
                'updated_at' => $lastMsg['created_at'] ?? '1970-01-01 00:00:00',
            ];
        }

        // Sort conversations by latest message timestamp DESC
        usort($conversations, static function (array $a, array $b): int {
            return strcmp($b['updated_at'], $a['updated_at']);
        });

        return $conversations;
    }

    /**
     * Alternative query using SQL JOIN for benchmark comparison (FR-27, Kriteria 6)
     */
    public static function getConversationsJoin(string $currentUserId): array
    {
        $db = DB::getInstance();
        $sql = "
            SELECT 
                u.id AS user_id,
                u.name AS real_name,
                u.phone,
                u.photo_path,
                c.alias_name,
                m.id AS last_message_id,
                m.body AS last_message_body,
                m.image_path AS last_message_image,
                m.created_at AS last_message_created_at,
                m.sender_id AS last_message_sender_id,
                m.read_at AS last_message_read_at
            FROM (
                SELECT 
                    CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id,
                    MAX(id) AS max_id
                FROM messages
                WHERE sender_id = ? OR receiver_id = ?
                GROUP BY partner_id
            ) AS latest
            INNER JOIN users u ON u.id = latest.partner_id
            LEFT JOIN contacts c ON c.owner_id = ? AND c.contact_user_id = u.id
            INNER JOIN messages m ON m.id = latest.max_id
            ORDER BY m.created_at DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserId]);
        return $stmt->fetchAll();
    }
}
