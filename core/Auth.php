<?php

declare(strict_types=1);

namespace Core;

class Auth
{
    private static ?array $user = null;

    /**
     * Check if a user is logged in.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Get the currently authenticated user array, or null.
     */
    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        $user = Session::get('user');
        if (!$user || !isset($user['id'], $user['role'])) {
            return null;
        }
        self::$user = $user;
        return self::$user;
    }

    /**
     * Get a specific field from the authenticated user.
     */
    public static function get(string $field, mixed $default = null): mixed
    {
        return self::user()[$field] ?? $default;
    }

    /**
     * Get the authenticated user's ID.
     */
    public static function id(): ?string
    {
        return self::get('id');
    }

    /**
     * Get the authenticated user's role.
     */
    public static function role(): ?string
    {
        return self::get('role');
    }

    /**
     * Check if user has one of the given roles.
     */
    public static function hasRole(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function isResident(): bool
    {
        return self::hasRole('resident');
    }

    public static function isStaff(): bool
    {
        return self::hasRole('captain', 'secretary', 'clerk');
    }

    public static function isCaptain(): bool
    {
        return self::hasRole('captain');
    }

    public static function isSecretary(): bool
    {
        return self::hasRole('captain', 'secretary');
    }

    /**
     * Get the Supabase access token stored in session.
     */
    public static function token(): ?string
    {
        return Session::get('access_token');
    }

    /**
     * Store authenticated user data in session after Supabase login.
     */
    public static function login(array $user, string $accessToken, string $refreshToken): void
    {
        Session::set('user', $user);
        Session::set('access_token', $accessToken);
        Session::set('refresh_token', $refreshToken);
        Session::set('_last_activity', time());
        self::$user = $user;
    }

    /**
     * Clear session and log out.
     */
    public static function logout(): void
    {
        self::$user = null;
        Session::destroy();
    }

    /**
     * Update the cached user profile in session (e.g. after profile edit).
     */
    public static function refreshUser(array $updatedUser): void
    {
        Session::set('user', $updatedUser);
        self::$user = $updatedUser;
    }
}
