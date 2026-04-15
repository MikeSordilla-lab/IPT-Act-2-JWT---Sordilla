<?php

declare(strict_types=1);

namespace App\Helpers;

final class Response
{
    public static function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    public static function success(int $statusCode, string $message, array $data = []): void
    {
        self::json($statusCode, [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function error(int $statusCode, string $message): void
    {
        self::json($statusCode, [
            'success' => false,
            'message' => $message,
        ]);
    }
}
