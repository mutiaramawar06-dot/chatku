<?php

declare(strict_types=1);

namespace Helpers;

class IdGenerator
{
    private const ENCODING_CHARS = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    /**
     * Generates a 26-character ULID (Universally Unique Lexicographically Sortable Identifier)
     * using cryptographically secure random_bytes() (FR-20, Kriteria 4).
     */
    public static function generateUlid(?int $timestampMs = null): string
    {
        if ($timestampMs === null) {
            $timestampMs = (int) (microtime(true) * 1000);
        }

        // 10 characters time component (48 bits)
        $timeChars = '';
        $timeVal = $timestampMs;
        for ($i = 9; $i >= 0; $i--) {
            $remainder = $timeVal % 32;
            $timeChars = self::ENCODING_CHARS[$remainder] . $timeChars;
            $timeVal = (int) ($timeVal / 32);
        }

        // 16 characters randomness component (80 bits) from random_bytes()
        $randomBytes = random_bytes(10);
        $randomChars = '';
        for ($i = 0; $i < 10; $i++) {
            $byte = ord($randomBytes[$i]);
            $randomChars .= self::ENCODING_CHARS[$byte % 32];
        }
        $extraBytes = random_bytes(6);
        for ($i = 0; $i < 6; $i++) {
            $byte = ord($extraBytes[$i]);
            $randomChars .= self::ENCODING_CHARS[$byte % 32];
        }

        return $timeChars . $randomChars;
    }

    /**
     * Generates a standard UUID v4 (36 characters) from random_bytes()
     */
    public static function generateUuidV4(): string
    {
        $data = random_bytes(16);
        // set version to 0100
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        // set bits 6-7 to 10
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
