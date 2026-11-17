<?php

declare(strict_types=1);

namespace Helpers;

class FileUploader
{
    private const MAX_SIZE_BYTES = 2 * 1024 * 1024; // 2 MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Validates and saves an uploaded image file securely (FR-15).
     *
     * @param array $file The $_FILES['...'] array
     * @param string $subDirectory Optional subdirectory inside uploads
     * @return array [success => bool, path => ?string, error => ?string, statusCode => int]
     */
    public static function uploadImage(array $file, string $subDirectory = ''): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Invalid file upload parameters',
                'statusCode' => 400,
            ];
        }

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'path' => null,
                'error' => null,
                'statusCode' => 200,
            ];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'File upload error code: ' . $file['error'],
                'statusCode' => 400,
            ];
        }

        // Check size limit (413 Payload Too Large)
        if ($file['size'] > self::MAX_SIZE_BYTES) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'File size exceeds maximum allowed size of 2MB',
                'statusCode' => 413,
            ];
        }

        // Validate true MIME type using finfo on raw file content (FR-15)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Server could not initialize MIME detector',
                'statusCode' => 500,
            ];
        }

        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Only genuine image files (JPEG, PNG, WEBP) are allowed. Unsupported type: ' . htmlspecialchars((string) $mimeType),
                'statusCode' => 415,
            ];
        }

        $extension = self::ALLOWED_MIME_TYPES[$mimeType];
        $randomFileName = bin2hex(random_bytes(16)) . '.' . $extension;

        $uploadRoot = dirname(__DIR__) . '/uploads';
        if ($subDirectory !== '') {
            $uploadRoot .= '/' . trim($subDirectory, '/\\');
        }

        if (!is_dir($uploadRoot) && !mkdir($uploadRoot, 0755, true) && !is_dir($uploadRoot)) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Failed to create upload destination directory',
                'statusCode' => 500,
            ];
        }

        $targetPath = $uploadRoot . '/' . $randomFileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Failed to move uploaded file',
                'statusCode' => 500,
            ];
        }

        $publicPath = '/uploads/' . ($subDirectory !== '' ? trim($subDirectory, '/\\') . '/' : '') . $randomFileName;

        return [
            'success' => true,
            'path' => $publicPath,
            'error' => null,
            'statusCode' => 201,
        ];
    }
}
