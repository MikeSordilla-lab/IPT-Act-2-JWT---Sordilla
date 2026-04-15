<?php

declare(strict_types=1);

namespace App\Config;

use mysqli;
use mysqli_sql_exception;
use RuntimeException;

final class Database
{
    public static function connect(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $host = self::env('DB_HOST', '127.0.0.1');
        $port = (int) self::env('DB_PORT', '3307');
        $name = self::env('DB_NAME', 'jwt_auth');
        $user = self::env('DB_USER', 'root');
        $pass = self::env('DB_PASS', '');

        try {
            $conn = new mysqli($host, $user, $pass, $name, $port);
            $conn->set_charset('utf8mb4');
            return $conn;
        } catch (mysqli_sql_exception $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    private static function env(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (isset($_ENV[$key]) && is_string($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }

        return $default;
    }
}
