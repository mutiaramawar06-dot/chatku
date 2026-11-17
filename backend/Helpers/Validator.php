<?php

declare(strict_types=1);

namespace Helpers;

class Validator
{
    /**
     * Validates phone number (FR-13):
     * Must not contain letters, digits only, optional leading '+', length 8-20 characters.
     */
    public static function validatePhone(string $phone): bool
    {
        $phone = trim($phone);
        return (bool) preg_match('/^\+?[0-9]{8,20}$/', $phone);
    }

    /**
     * Validates person name (FR-14):
     * Must not contain any digits (0-9).
     */
    public static function validateName(string $name): bool
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            return false;
        }

        // Must not contain any numbers (FR-14)
        return !preg_match('/[0-9]/', $name);
    }

    /**
     * Validates password strength (minimum 6 chars).
     */
    public static function validatePassword(string $password): bool
    {
        return strlen($password) >= 6;
    }
}
