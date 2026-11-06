<?php

declare(strict_types=1);

namespace App\Utilities\Http;

use App\Config\App\AppConfig;

/** Immutable view of the current HTTP request: path, method, query, JSON or form body. */
final class Request
{
    private array $body;
    private array $routeParams = [];

    public function __construct()
    {
        $this->body = $this->parseBody();
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /** Path relative to the app base path, always starting with "/". */
    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = AppConfig::basePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . trim($path, '/');
        return $path === '/index.php' ? '/' : $path;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        $value = $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public function queryAll(): array
    {
        return $_GET;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->body[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    public function all(): array
    {
        return $this->body;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? null;
    }

    public function isApi(): bool
    {
        return str_starts_with($this->path(), '/api/');
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function param(string $key): string
    {
        return (string) ($this->routeParams[$key] ?? '');
    }

    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode((string) file_get_contents('php://input'), true);
            return is_array($decoded) ? $decoded : [];
        }
        return $_POST;
    }
}
