<?php

declare(strict_types=1);

namespace Controllers;

use Helpers\FileUploader;
use Helpers\Validator;
use Models\User;
use Services\AuthService;
use function Lib\responseJson;

class AuthController
{
    /**
     * Helper to parse request input from JSON or FormData.
     */
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
     * Register a new user (FR-01, FR-05, FR-13, FR-14, FR-15, FR-20, FR-25).
     * Status codes: 201 Created, 400 Bad Request, 409 Conflict, 413/415 File, 422 Unprocessable Entity.
     */
    public static function register(): void
    {
        $data = self::getRequestData();

        $name = trim((string) ($data['name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $errors = [];

        // FR-14: Name cannot contain numbers
        if ($name === '') {
            $errors['name'] = 'Name is required';
        } elseif (!Validator::validateName($name)) {
            $errors['name'] = 'Name must be at least 2 characters and cannot contain numbers';
        }

        // FR-13: Phone digits only, optional +, 8-20 chars
        if ($phone === '') {
            $errors['phone'] = 'Phone number is required';
        } elseif (!Validator::validatePhone($phone)) {
            $errors['phone'] = 'Phone number must contain only digits (optional leading +) and be between 8-20 digits';
        }

        // Password min 6 chars
        if ($password === '') {
            $errors['password'] = 'Password is required';
        } elseif (!Validator::validatePassword($password)) {
            $errors['password'] = 'Password must be at least 6 characters';
        }

        if (!empty($errors)) {
            responseJson(
                statusCode: 422,
                message: 'Validation failed',
                errors: $errors
            );
        }

        // Handle profile photo upload (FR-15)
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = FileUploader::uploadImage($_FILES['photo'], 'profiles');
            if (!$uploadResult['success']) {
                responseJson(
                    statusCode: $uploadResult['statusCode'],
                    message: $uploadResult['error'],
                    errors: ['photo' => $uploadResult['error']]
                );
            }
            $photoPath = $uploadResult['path'];
        }

        try {
            $user = User::createWithLock([
                'name' => $name,
                'phone' => $phone,
                'password' => $password,
                'photo_path' => $photoPath,
            ]);

            responseJson(
                data: $user,
                statusCode: 201,
                message: 'User registered successfully'
            );
        } catch (\RuntimeException $e) {
            responseJson(
                statusCode: $e->getCode() === 409 ? 409 : 500,
                message: $e->getMessage(),
                errors: ['phone' => $e->getMessage()]
            );
        }
    }

    /**
     * User Login (FR-02, FR-05).
     * Status codes: 200 OK, 401 Unauthorized, 422 Validation.
     */
    public static function login(): void
    {
        $data = self::getRequestData();

        $phone = trim((string) ($data['phone'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $errors = [];
        if ($phone === '') {
            $errors['phone'] = 'Phone number is required';
        }
        if ($password === '') {
            $errors['password'] = 'Password is required';
        }

        if (!empty($errors)) {
            responseJson(
                statusCode: 422,
                message: 'Validation failed',
                errors: $errors
            );
        }

        $user = User::findForAuth($phone);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            responseJson(
                statusCode: 401,
                message: 'Invalid phone number or password',
                errors: ['auth' => 'Invalid credentials']
            );
        }

        $userData = [
            'id' => $user['id'],
            'name' => $user['name'],
            'phone' => $user['phone'],
            'photo_path' => $user['photo_path'],
        ];

        $token = AuthService::generateToken($userData);

        responseJson(
            data: [
                'token' => $token,
                'user' => $userData,
            ],
            statusCode: 200,
            message: 'Login successful'
        );
    }

    /**
     * Get current authenticated user profile (FR-04).
     */
    public static function me(): void
    {
        $currentUser = AuthService::authenticate();
        responseJson(
            data: $currentUser,
            statusCode: 200,
            message: 'User profile retrieved successfully'
        );
    }

    /**
     * Delete account (FR-03).
     */
    public static function deleteAccount(): void
    {
        $currentUser = AuthService::authenticate();
        User::deleteById($currentUser['id']);

        responseJson(
            statusCode: 200,
            message: 'Account deleted successfully'
        );
    }
}
