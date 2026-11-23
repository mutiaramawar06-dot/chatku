<?php

declare(strict_types=1);

use Controllers\AuthController;
use Controllers\ChatController;
use Controllers\ContactController;
use Controllers\MessageController;
use Lib\DB;
use Lib\Router;
use function Lib\responseJson;

return static function (Router $router): void {
    // Health check endpoint (FR-19: safe error handling, no internal leak)
    $router->get('/api/health', static function (): void {
        try {
            $db = DB::getInstance();
            $db->query('SELECT 1');
            responseJson(
                data: ['ok' => true, 'database' => true],
                statusCode: 200,
                message: 'API is running'
            );
        } catch (\Throwable $e) {
            error_log('Database health check failed: ' . $e->getMessage());
            responseJson(
                data: ['ok' => false, 'database' => false],
                statusCode: 500,
                message: 'Database service unavailable',
                errors: ['database' => 'Unable to connect to database service']
            );
        }
    });

    // Authentication & Account Endpoints (Kriteria 2 & 4)
    $router->post('/api/auth/register', [AuthController::class, 'register']);
    $router->post('/api/auth/login', [AuthController::class, 'login']);
    $router->get('/api/me', [AuthController::class, 'me']);
    $router->delete('/api/account', [AuthController::class, 'deleteAccount']);

    // Contacts Endpoints (Kriteria 2 & 3)
    $router->get('/api/contacts', [ContactController::class, 'index']);
    $router->post('/api/contacts', [ContactController::class, 'store']);
    $router->get('/api/contacts/{id}', [ContactController::class, 'show']);
    $router->put('/api/contacts/{id}', [ContactController::class, 'update']);
    $router->delete('/api/contacts/{id}', [ContactController::class, 'destroy']);

    // Messages & Conversations Endpoints (Kriteria 3 & 5)
    $router->get('/api/conversations', [MessageController::class, 'conversations']);
    $router->get('/api/messages/{userId}', [MessageController::class, 'history']);
    $router->post('/api/messages', [MessageController::class, 'store']);

    // Direct Image Upload Endpoint (ChatController)
    $router->post('/api/chat/image', [ChatController::class, 'uploadImage']);

    // Device Token for FCM Push Notification (Kriteria 6)
    $router->post('/api/device-token', [MessageController::class, 'saveDeviceToken']);
};
