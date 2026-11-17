<?php

declare(strict_types=1);

namespace Controllers;

use Helpers\FileUploader;
use Models\DeviceToken;
use Models\Message;
use Models\User;
use Services\AuthService;
use Services\NotificationService;
use function Lib\responseJson;

class MessageController
{
    private static function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw ?: '', true);
            return is_array($decoded) ? $decoded : [];
        }
        return $_POST;
    }

    /**
     * Send a message to another user (FR-08, FR-15, FR-29).
     * Status codes: 201 Created, 404 Receiver Not Found, 413/415 File error, 422 Validation.
     */
    public static function store(): void
    {
        $currentUser = AuthService::authenticate();
        $data = self::getRequestData();

        $receiverId = trim((string) ($data['receiver_id'] ?? ''));
        $receiverPhone = trim((string) ($data['receiver_phone'] ?? ''));
        $body = trim((string) ($data['body'] ?? ''));

        // Resolve receiver
        $receiver = null;
        if ($receiverId !== '') {
            $receiver = User::findById($receiverId);
        } elseif ($receiverPhone !== '') {
            $receiver = User::findByPhone($receiverPhone);
        }

        if (!$receiver) {
            responseJson(
                statusCode: 404,
                message: 'Recipient user not found',
                errors: ['receiver' => 'User does not exist']
            );
        }

        // Cannot send message to self (FR-07/08)
        if ($receiver['id'] === $currentUser['id']) {
            responseJson(
                statusCode: 422,
                message: 'You cannot send a message to yourself',
                errors: ['receiver' => 'Self messaging not allowed']
            );
        }

        // Handle image upload if provided (FR-15)
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = FileUploader::uploadImage($_FILES['image'], 'messages');
            if (!$uploadResult['success']) {
                responseJson(
                    statusCode: $uploadResult['statusCode'],
                    message: $uploadResult['error'],
                    errors: ['image' => $uploadResult['error']]
                );
            }
            $imagePath = $uploadResult['path'];
        }

        // Must provide at least text body or an image
        if ($body === '' && $imagePath === null) {
            responseJson(
                statusCode: 422,
                message: 'Message cannot be completely empty. Please enter text or attach an image.',
                errors: ['message' => 'Empty message content']
            );
        }

        $createdMessage = Message::create(
            senderId: $currentUser['id'],
            receiverId: $receiver['id'],
            body: $body !== '' ? $body : null,
            imagePath: $imagePath
        );

        // Remote push notification trigger (FR-29)
        $previewText = $body !== '' ? $body : '[Foto]';
        NotificationService::notifyNewMessage($receiver['id'], $currentUser['name'], $previewText);

        responseJson(
            data: $createdMessage,
            statusCode: 201,
            message: 'Message sent successfully'
        );
    }

    /**
     * Get conversation history between current user and target user (FR-09, FR-12).
     */
    public static function history(string $userId): void
    {
        $currentUser = AuthService::authenticate();
        $targetUserId = trim($userId);

        $targetUser = User::findById($targetUserId);
        if (!$targetUser) {
            responseJson(
                statusCode: 404,
                message: 'Target user not found'
            );
        }

        // FR-12: Current user can only read conversations they are a participant of
        $history = Message::getHistoryBetween($currentUser['id'], $targetUserId);

        responseJson(
            data: [
                'partner' => [
                    'id' => $targetUser['id'],
                    'name' => $targetUser['name'],
                    'phone' => $targetUser['phone'],
                    'photo_path' => $targetUser['photo_path'],
                ],
                'messages' => $history,
            ],
            statusCode: 200,
            message: 'Message history retrieved successfully'
        );
    }

    /**
     * Get list of conversations with latest messages using Eager Loading (FR-10, FR-23).
     */
    public static function conversations(): void
    {
        $currentUser = AuthService::authenticate();
        $conversations = Message::getConversationsEagerLoading($currentUser['id']);

        responseJson(
            data: $conversations,
            statusCode: 200,
            message: 'Conversations retrieved successfully'
        );
    }

    /**
     * Save FCM device token for push notification (FR-29).
     */
    public static function saveDeviceToken(): void
    {
        $currentUser = AuthService::authenticate();
        $data = self::getRequestData();

        $token = trim((string) ($data['fcm_token'] ?? ''));
        if ($token === '') {
            responseJson(
                statusCode: 422,
                message: 'fcm_token is required',
                errors: ['fcm_token' => 'Token cannot be empty']
            );
        }

        DeviceToken::saveToken($currentUser['id'], $token);

        responseJson(
            statusCode: 201,
            message: 'Device token saved successfully'
        );
    }
}
