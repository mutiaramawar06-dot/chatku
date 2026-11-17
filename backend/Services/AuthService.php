<?php

declare(strict_types=1);

namespace Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Models\User;
use function Lib\responseJson;

class AuthService
{
    private static function getSecret(): string
    {
        $secret = getenv('JWT_SECRET');
        return !empty($secret) ? $secret : 'default_super_secret_jwt_key_cpk_2026';
    }

    private static function getExpirySeconds(): int
    {
        $expiry = getenv('JWT_EXPIRY_SECONDS');
        return !empty($expiry) ? (int) $expiry : 86400; // 24 hours
    }

    /**
     * Issues a JWT token for a given user array.
     */
    public static function generateToken(array $user): string
    {
        $issuedAt = time();
        $expireAt = $issuedAt + self::getExpirySeconds();

        $payload = [
            'iss' => 'chat-app',
            'iat' => $issuedAt,
            'exp' => $expireAt,
            'sub' => $user['id'],
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'phone' => $user['phone'],
                'photo_path' => $user['photo_path'] ?? null,
            ],
        ];

        return JWT::encode($payload, self::getSecret(), 'HS256');
    }

    /**
     * Decodes and validates a JWT token. Returns payload or null.
     */
    public static function decodeToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecret(), 'HS256'));
            return (array) $decoded;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Extracts token from Authorization: Bearer <token> header.
     */
    public static function getBearerToken(): ?string
    {
        $header = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $header = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $header = trim($headers['Authorization']);
            } elseif (isset($headers['authorization'])) {
                $header = trim($headers['authorization']);
            }
        }

        if ($header && preg_match('/Bearer\s(\S+)/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Middleware check: verifies JWT token, loads user from database,
     * and returns the authenticated user data.
     * If unauthenticated, halts with 401 Unauthorized (FR-04, FR-19).
     */
    public static function authenticate(): array
    {
        $token = self::getBearerToken();
        if (!$token) {
            responseJson(
                statusCode: 401,
                message: 'Unauthorized: missing Bearer token in Authorization header',
                errors: ['auth' => 'Token not provided']
            );
        }

        $decoded = self::decodeToken($token);
        if (!$decoded || !isset($decoded['sub'])) {
            responseJson(
                statusCode: 401,
                message: 'Unauthorized: invalid or expired token',
                errors: ['auth' => 'Token invalid or expired']
            );
        }

        $userId = (string) $decoded['sub'];
        $user = User::findById($userId);
        if (!$user) {
            responseJson(
                statusCode: 401,
                message: 'Unauthorized: user account no longer exists',
                errors: ['auth' => 'Account not found']
            );
        }

        return $user;
    }
}
