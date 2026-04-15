<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Config\Jwt;
use mysqli;
use Throwable;

final class AuthMiddleware
{
    public static function validateToken(mysqli $db): object
    {
        $token = self::extractBearerToken();
        if ($token === null) {
            throw new \RuntimeException('Missing Bearer token.');
        }

        try {
            $payload = Jwt::decodeToken($token);
        } catch (Throwable $e) {
            if (str_contains(strtolower($e->getMessage()), 'expired')) {
                throw new \RuntimeException('Token expired.');
            }
            throw new \RuntimeException('Invalid token.');
        }

        $jti = (string) ($payload->jti ?? '');
        if ($jti === '') {
            throw new \RuntimeException('Token missing jti claim.');
        }

        $stmt = $db->prepare('SELECT id FROM revoked_tokens WHERE jti = ? LIMIT 1');
        $stmt->bind_param('s', $jti);
        $stmt->execute();
        $revoked = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($revoked) {
            throw new \RuntimeException('Token has been revoked.');
        }

        return $payload;
    }

    private static function extractBearerToken(): ?string
    {
        $headers = self::headers();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!is_string($auth)) {
            return null;
        }

        if (!preg_match('/^Bearer\s+(.*)$/i', $auth, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private static function headers(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            return is_array($headers) ? $headers : [];
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }
}
