<?php

declare(strict_types=1);

namespace Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(SESSION_NAME);

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => (APP_ENV === 'production'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['_last_regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['_last_regenerated'] = time();
        } elseif (time() - $_SESSION['_last_regenerated'] > 300) {
            session_regenerate_id(true);
            $_SESSION['_last_regenerated'] = time();
        }

        // Staff inactivity timeout
        self::checkInactivityTimeout();
    }

    private static function checkInactivityTimeout(): void
    {
        $user = self::get('user');
        if (!$user) {
            return;
        }

        $isStaff = in_array($user['role'] ?? '', ['captain', 'secretary', 'clerk'], true);
        if (!$isStaff) {
            return;
        }

        $lastActivity = self::get('_last_activity');
        if ($lastActivity && (time() - $lastActivity) > SESSION_LIFETIME) {
            self::destroy();
            return;
        }

        self::set('_last_activity', time());
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    // ── Flash Messages ────────────────────────────────────

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    // ── CSRF ──────────────────────────────────────────────

    public static function generateCsrfToken(): string
    {
        if (!self::has('_csrf_token')) {
            self::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('_csrf_token');
    }

    public static function validateCsrfToken(string $token): bool
    {
        $stored = self::get('_csrf_token');
        if (!$stored) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function rotateCsrfToken(): void
    {
        self::set('_csrf_token', bin2hex(random_bytes(32)));
    }
}
