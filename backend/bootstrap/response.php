<?php

declare(strict_types=1);

namespace Lib;

function responseJson(
    array|object|null $data = null,
    int $statusCode = 200,
    string $message = '',
    ?bool $success = null,
    array|string|null $errors = null
): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
    }

    $isSuccess = $success !== null ? $success : ($statusCode >= 200 && $statusCode < 400);

    $payload = [
        'success' => $isSuccess,
        'message' => $message,
    ];

    if ($data !== null) {
        $payload['data'] = $data;
    }

    if ($errors !== null) {
        $payload['errors'] = $errors;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
