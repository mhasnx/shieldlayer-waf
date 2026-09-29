<?php

namespace ShieldLayer\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $body;
    private array $query;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUrl = parse_url($rawUri, PHP_URL_PATH);
        
        // Strip project path prefix if running in subdirectory like 
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = str_replace('\\', '/', $scriptDir);
        if ($scriptDir !== '/' && !empty($scriptDir) && str_starts_with($parsedUrl, $scriptDir)) {
            $parsedUrl = substr($parsedUrl, strlen($scriptDir));
        }

        $this->uri = '/' . trim($parsedUrl ?: '/', '/');
        $this->query = $_GET;
        $this->body = $_POST;
        $this->headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }
}
