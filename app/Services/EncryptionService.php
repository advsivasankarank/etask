<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class EncryptionService
{
    public function encrypt(?string $plainText): ?array
    {
        $plainText = $plainText !== null ? trim($plainText) : null;
        if ($plainText === null || $plainText === '') {
            return null;
        }

        $iv = random_bytes(16);
        $cipherText = openssl_encrypt(
            $plainText,
            'AES-256-CBC',
            $this->key(),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($cipherText === false) {
            throw new RuntimeException('Unable to encrypt sensitive data.');
        }

        return [
            'ciphertext' => base64_encode($cipherText),
            'iv' => base64_encode($iv),
        ];
    }

    public function decrypt(?string $cipherText, ?string $iv): ?string
    {
        if ($cipherText === null || $cipherText === '' || $iv === null || $iv === '') {
            return null;
        }

        $decodedCipher = base64_decode($cipherText, true);
        $decodedIv = base64_decode($iv, true);

        if ($decodedCipher === false || $decodedIv === false) {
            return null;
        }

        $plainText = openssl_decrypt(
            $decodedCipher,
            'AES-256-CBC',
            $this->key(),
            OPENSSL_RAW_DATA,
            $decodedIv
        );

        return $plainText === false ? null : $plainText;
    }

    private function key(): string
    {
        $key = trim((string) config('app.encryption_key', ''));
        if ($key === '') {
            throw new RuntimeException('APP_KEY must be configured before sensitive data can be encrypted.');
        }

        if (strlen($key) < 32 || str_contains(strtolower($key), 'replace_with')) {
            throw new RuntimeException('APP_KEY must be a strong non-placeholder secret of at least 32 characters.');
        }

        return hash('sha256', $key, true);
    }
}
