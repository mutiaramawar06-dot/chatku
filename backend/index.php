<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Safely serve uploaded media in development
if (str_starts_with($uri, '/uploads/')) {
    $filePath = __DIR__ . str_replace(['../', '..\\'], '', $uri);
    if (is_file($filePath)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $filePath) : null;
        if ($finfo) {
            finfo_close($finfo);
        }
        header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
        header('Content-Length: ' . (string) filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        readfile($filePath);
        exit;
    }
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Uploaded file not found']);
    exit;
}

$router = require __DIR__ . '/bootstrap/index.php';
$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $uri
);
