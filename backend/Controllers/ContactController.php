<?php

declare(strict_types=1);

namespace Controllers;

use Helpers\Validator;
use Models\Contact;
use Models\User;
use Services\AuthService;
use function Lib\responseJson;

class ContactController
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
     * List all contacts for the current authenticated user (FR-06).
     */
    public static function index(): void
    {
        $currentUser = AuthService::authenticate();
        $contacts = Contact::getAllByOwner($currentUser['id']);

        responseJson(
            data: $contacts,
            statusCode: 200,
            message: 'Contacts retrieved successfully'
        );
    }

    /**
     * Add a contact with verification (FR-06, FR-07, FR-13, FR-14).
     * Rejects unregistered numbers (404), self-contacts (422), duplicates (409).
     */
    public static function store(): void
    {
        $currentUser = AuthService::authenticate();
        $data = self::getRequestData();

        $phone = trim((string) ($data['phone'] ?? ''));
        $aliasName = trim((string) ($data['alias_name'] ?? ''));

        $errors = [];
        if ($phone === '') {
            $errors['phone'] = 'Phone number is required';
        } elseif (!Validator::validatePhone($phone)) {
            $errors['phone'] = 'Invalid phone number format';
        }

        if ($aliasName !== '' && !Validator::validateName($aliasName)) {
            $errors['alias_name'] = 'Alias name cannot contain numbers and must be at least 2 characters';
        }

        if (!empty($errors)) {
            responseJson(
                statusCode: 422,
                message: 'Validation failed',
                errors: $errors
            );
        }

        // Check if target user exists in database (FR-07)
        $targetUser = User::findByPhone($phone);
        if (!$targetUser) {
            responseJson(
                statusCode: 404,
                message: "User with phone number '{$phone}' is not registered in the system",
                errors: ['phone' => 'Unregistered user phone number']
            );
        }

        // Cannot add self as contact (FR-07)
        if ($targetUser['id'] === $currentUser['id']) {
            responseJson(
                statusCode: 422,
                message: 'You cannot add your own number as a contact',
                errors: ['phone' => 'Self contact not allowed']
            );
        }

        // Cannot add duplicate contact (FR-07)
        $existing = Contact::findByOwnerAndContactUser($currentUser['id'], $targetUser['id']);
        if ($existing) {
            responseJson(
                statusCode: 409,
                message: 'This user is already in your contact list',
                errors: ['contact' => 'Duplicate contact']
            );
        }

        $finalAlias = $aliasName !== '' ? $aliasName : $targetUser['name'];
        $newContactId = Contact::create($currentUser['id'], $targetUser['id'], $finalAlias);

        $createdContact = Contact::findByIdAndOwner($newContactId, $currentUser['id']);

        responseJson(
            data: $createdContact,
            statusCode: 201,
            message: 'Contact added successfully'
        );
    }

    /**
     * Get single contact detail (FR-06).
     */
    public static function show(string $id): void
    {
        $currentUser = AuthService::authenticate();
        $contactId = (int) $id;

        $contact = Contact::findByIdAndOwner($contactId, $currentUser['id']);
        if (!$contact) {
            responseJson(
                statusCode: 404,
                message: 'Contact not found'
            );
        }

        responseJson(
            data: $contact,
            statusCode: 200,
            message: 'Contact details retrieved successfully'
        );
    }

    /**
     * Update contact (FR-06, FR-07).
     */
    public static function update(string $id): void
    {
        $currentUser = AuthService::authenticate();
        $contactId = (int) $id;

        $existing = Contact::findByIdAndOwner($contactId, $currentUser['id']);
        if (!$existing) {
            responseJson(
                statusCode: 404,
                message: 'Contact not found'
            );
        }

        $data = self::getRequestData();
        $aliasName = trim((string) ($data['alias_name'] ?? $existing['alias_name']));
        $phone = isset($data['phone']) ? trim((string) $data['phone']) : null;

        $errors = [];
        if ($aliasName === '') {
            $errors['alias_name'] = 'Alias name cannot be empty';
        } elseif (!Validator::validateName($aliasName)) {
            $errors['alias_name'] = 'Alias name cannot contain numbers and must be at least 2 characters';
        }

        $newContactUserId = null;
        if ($phone !== null && $phone !== '' && $phone !== $existing['user_phone']) {
            if (!Validator::validatePhone($phone)) {
                $errors['phone'] = 'Invalid phone number format';
            } else {
                $targetUser = User::findByPhone($phone);
                if (!$targetUser) {
                    responseJson(
                        statusCode: 404,
                        message: "User with phone number '{$phone}' is not registered",
                        errors: ['phone' => 'Unregistered user phone number']
                    );
                }

                if ($targetUser['id'] === $currentUser['id']) {
                    responseJson(
                        statusCode: 422,
                        message: 'You cannot set your own phone number as a contact',
                        errors: ['phone' => 'Self contact not allowed']
                    );
                }

                $dup = Contact::findByOwnerAndContactUser($currentUser['id'], $targetUser['id']);
                if ($dup && (int) $dup['id'] !== $contactId) {
                    responseJson(
                        statusCode: 409,
                        message: 'Another contact with this phone number already exists',
                        errors: ['phone' => 'Duplicate contact']
                    );
                }

                $newContactUserId = $targetUser['id'];
            }
        }

        if (!empty($errors)) {
            responseJson(
                statusCode: 422,
                message: 'Validation failed',
                errors: $errors
            );
        }

        Contact::update($contactId, $currentUser['id'], $aliasName, $newContactUserId);
        $updated = Contact::findByIdAndOwner($contactId, $currentUser['id']);

        responseJson(
            data: $updated,
            statusCode: 200,
            message: 'Contact updated successfully'
        );
    }

    /**
     * Delete contact (FR-06).
     */
    public static function destroy(string $id): void
    {
        $currentUser = AuthService::authenticate();
        $contactId = (int) $id;

        $deleted = Contact::delete($contactId, $currentUser['id']);
        if (!$deleted) {
            responseJson(
                statusCode: 404,
                message: 'Contact not found'
            );
        }

        responseJson(
            statusCode: 200,
            message: 'Contact deleted successfully'
        );
    }
}
