<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

final class Jwt
{
    public static function issueToken(int $userId, string $email): string
    {
        $secret = self::secret();
        $algo = self::algo();
        $ttl = self::ttl();
        $iat = time();
        $exp = $iat + $ttl;

        $payload = [
            'sub' => $userId,
            'email' => $email,
            'iat' => $iat,
            'exp' => $exp,
            'jti' => bin2hex(random_bytes(16)),
        ];

        $jwtClass = 'Firebase\\JWT\\JWT';
        if (!class_exists($jwtClass)) {
            throw new RuntimeException('firebase/php-jwt is not installed. Run composer install.');
        }

        /** @var string $token */
        $token = $jwtClass::encode($payload, $secret, $algo);
        return $token;
    }

    public static function decodeToken(string $token): object
    {
        $jwtClass = 'Firebase\\JWT\\JWT';
        $keyClass = 'Firebase\\JWT\\Key';

        if (!class_exists($jwtClass) || !class_exists($keyClass)) {
            throw new RuntimeException('firebase/php-jwt is not installed. Run composer install.');
        }

        $key = new $keyClass(self::secret(), self::algo());
        return $jwtClass::decode($token, $key);
    }

    public static function secret(): string
    {
        $secret = self::env('JWT_SECRET', '');
        if ($secret === '') {
            throw new RuntimeException('JWT_SECRET is not configured.');
        }
        return $secret;
    }

    public static function algo(): string
    {
        return self::env('JWT_ALGO', 'HS256');
    }

    public static function ttl(): int
    {
        return (int) self::env('JWT_TTL_SECONDS', '3600');
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
