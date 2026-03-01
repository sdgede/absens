<?php

namespace App\Services;

use RuntimeException;

class FaceService
{
    private const CIPHER = 'AES-256-CBC';
    private const IV_LENGTH = 16;

    /**
     * Encrypt a face embedding array.
     *
     * Encodes the array to JSON, encrypts with AES-256-CBC using a random IV,
     * and returns base64(iv + ciphertext).
     */
    public function encryptEmbedding(array $embedding): string
    {
        $key        = config('attendance.embedding_cipher_key') ?? env('EMBEDDING_CIPHER_KEY');
        $iv         = openssl_random_pseudo_bytes(self::IV_LENGTH);
        $json       = json_encode($embedding);
        $encrypted  = openssl_encrypt($json, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new RuntimeException('Failed to encrypt face embedding.');
        }

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a face embedding cipher back to an array.
     *
     * Expects the input to be base64(iv + ciphertext) as produced by encryptEmbedding().
     */
    public function decryptEmbedding(string $cipher): array
    {
        $key       = config('attendance.embedding_cipher_key') ?? env('EMBEDDING_CIPHER_KEY');
        $decoded   = base64_decode($cipher, true);

        if ($decoded === false || strlen($decoded) <= self::IV_LENGTH) {
            throw new RuntimeException('Invalid cipher format for face embedding.');
        }

        $iv         = substr($decoded, 0, self::IV_LENGTH);
        $ciphertext = substr($decoded, self::IV_LENGTH);

        $decrypted = openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new RuntimeException('Failed to decrypt face embedding.');
        }

        $result = json_decode($decrypted, true);

        if (!is_array($result)) {
            throw new RuntimeException('Decrypted data is not a valid embedding array.');
        }

        return $result;
    }

    /**
     * Validate a face embedding array.
     *
     * Rules:
     *  - Must contain exactly 128 elements.
     *  - Must not be all zeros.
     *  - Every element must be a float in the range [-1.0, 1.0].
     */
    public function validateEmbedding(array $embedding): bool
    {
        // Rule 1: Must have exactly 128 dimensions
        if (count($embedding) !== 128) {
            return false;
        }

        $allZero = true;

        foreach ($embedding as $value) {
            // Rule 3: Each value must be a numeric float within [-1.0, 1.0]
            if (!is_float($value) && !is_int($value)) {
                return false;
            }

            $float = (float) $value;

            if ($float < -1.0 || $float > 1.0) {
                return false;
            }

            if ($float !== 0.0) {
                $allZero = false;
            }
        }

        // Rule 2: Must not be all zeros
        if ($allZero) {
            return false;
        }

        return true;
    }
}
