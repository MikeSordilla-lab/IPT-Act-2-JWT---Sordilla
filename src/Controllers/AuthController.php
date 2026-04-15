<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Jwt;
use App\Helpers\Response;
use mysqli;
use RuntimeException;

final class AuthController
{
    public static function register(mysqli $db): void
    {
        $input = self::jsonInput();

        $name = trim((string) ($input['name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            Response::error(400, 'name, email, and password are required.');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error(400, 'Invalid email format.');
            return;
        }

        $check = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->bind_param('s', $email);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();
        $check->close();

        if ($exists) {
            Response::error(409, 'Email already registered.');
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $insert->bind_param('sss', $name, $email, $hashedPassword);
        $insert->execute();
        $insert->close();

        Response::success(201, 'User registered successfully.');
    }

    public static function login(mysqli $db): void
    {
        $input = self::jsonInput();

        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($email === '' || $password === '') {
            Response::error(400, 'email and password are required.');
            return;
        }

        $stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, (string) $user['password'])) {
            Response::error(401, 'Invalid credentials.');
            return;
        }

        $token = Jwt::issueToken((int) $user['id'], (string) $user['email']);

        Response::success(200, 'Login successful.', [
            'token' => $token,
            'user' => [
                'id' => (int) $user['id'],
                'name' => (string) $user['name'],
                'email' => (string) $user['email'],
            ],
        ]);
    }

    public static function protected(object $payload): void
    {
        Response::success(200, 'Authorized access.', [
            'user_id' => (int) ($payload->sub ?? 0),
            'email' => (string) ($payload->email ?? ''),
            'expires_at' => (int) ($payload->exp ?? 0),
        ]);
    }

    public static function logout(mysqli $db, object $payload): void
    {
        $jti = (string) ($payload->jti ?? '');
        $exp = (int) ($payload->exp ?? 0);

        if ($jti === '' || $exp <= 0) {
            throw new RuntimeException('Token missing jti/exp claims.');
        }

        $expiresAt = date('Y-m-d H:i:s', $exp);
        $stmt = $db->prepare('INSERT IGNORE INTO revoked_tokens (jti, expires_at) VALUES (?, ?)');
        $stmt->bind_param('ss', $jti, $expiresAt);
        $stmt->execute();
        $stmt->close();

        Response::success(200, 'Logout successful. Token revoked.');
    }

    private static function jsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
