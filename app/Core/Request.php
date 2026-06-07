<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private array $routeParams = [];

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = str_replace('\\', '/', dirname($scriptName));

        if ($baseDir !== '/' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir));
        }

        $normalized = '/' . trim($uri, '/');
        return $normalized === '//' ? '/' : $normalized;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->routeParams)) {
            return $this->sanitizeValue($this->routeParams[$key]);
        }

        if (array_key_exists($key, $_POST)) {
            return $this->sanitizeValue($this->readInputValue(INPUT_POST, $key, $_POST[$key]));
        }

        if (array_key_exists($key, $_GET)) {
            return $this->sanitizeValue($this->readInputValue(INPUT_GET, $key, $_GET[$key]));
        }

        return $default;
    }

    public function all(): array
    {
        $data = $this->routeParams;

        foreach ($_GET as $key => $value) {
            $data[$key] = $this->sanitizeValue($this->readInputValue(INPUT_GET, (string) $key, $value));
        }

        foreach ($_POST as $key => $value) {
            $data[$key] = $this->sanitizeValue($this->readInputValue(INPUT_POST, (string) $key, $value));
        }

        return $data;
    }

    public function setRouteParams(array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }

    public function file(string $key): array|null
    {
        return $_FILES[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        if (!isset($_FILES[$key])) {
            return false;
        }

        $file = $_FILES[$key];

        if (is_array($file['name'] ?? null)) {
            foreach ($file['name'] as $index => $name) {
                if ($name !== '' && ($file['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                    return true;
                }
            }

            return false;
        }

        return ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function ip(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public function userAgent(): ?string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        return is_string($userAgent) ? $this->sanitizeString($userAgent) : null;
    }

    private function readInputValue(int $type, string $key, mixed $fallback): mixed
    {
        if (is_array($fallback)) {
            return $fallback;
        }

        $value = filter_input($type, $key, FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
        return $value ?? $fallback;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $key => $item) {
                $sanitized[$key] = $this->sanitizeValue($item);
            }

            return $sanitized;
        }

        if (is_string($value)) {
            return $this->sanitizeString($value);
        }

        return $value;
    }

    private function sanitizeString(string $value): string
    {
        $value = str_replace("\0", '', $value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;

        return trim($value);
    }
}
