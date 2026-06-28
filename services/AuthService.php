<?php

declare(strict_types=1);

namespace Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AuthService
{
    private Client $client;
    // HIGH-02: Removed dead $db property. SupabaseService was instantiated on every
    // login but never used inside AuthService — wasted a full Guzzle client + object
    // allocation on every authentication call. Controllers that need DB access after
    // login should instantiate SupabaseService themselves.

    public function __construct()
    {
        $this->client = new Client(['timeout' => 15]);
    }

    /**
     * Register a new user via Supabase Auth.
     * Returns ['success' => true, 'user' => [...]] or ['success' => false, 'error' => '...']
     *
     * LOW-08: Supabase signup errors are nested under $data['error_description'] or
     * $data['msg'], not $data['error']['message']. Fixed to check the real keys.
     */
    public function register(string $email, string $password): array
    {
        try {
            $response = $this->client->post(SUPABASE_AUTH_URL . '/signup', [
                'headers' => [
                    'apikey'       => SUPABASE_ANON_KEY,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email'    => $email,
                    'password' => $password,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true) ?? [];

            // LOW-08: Supabase returns error info in different keys depending on endpoint
            if (!empty($data['error']) || !empty($data['error_description'])) {
                $errorMsg = $data['error_description']
                    ?? $data['msg']
                    ?? (is_string($data['error']) ? $data['error'] : 'Registration failed.');
                return ['success' => false, 'error' => $errorMsg];
            }

            // CRITICAL-04: Previously accessed $data['user']['id'] without null checks,
            // causing a fatal error if Supabase returned an unexpected response shape.
            if (empty($data['id']) && empty($data['user']['id'])) {
                return ['success' => false, 'error' => 'Registration failed. Please try again.'];
            }

            return ['success' => true, 'user' => $data];
        } catch (GuzzleException $e) {
            $this->logError('register', $e);
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * Sign in with email and password.
     *
     * CRITICAL-04: The original code accessed $data['access_token'],
     * $data['refresh_token'], and $data['user']['id'] without null-checking,
     * causing a fatal TypeError when Supabase returned an error body (e.g.
     * wrong password). All keys are now checked before access.
     */
    public function login(string $email, string $password): array
    {
        try {
            $response = $this->client->post(SUPABASE_AUTH_URL . '/token?grant_type=password', [
                'headers' => [
                    'apikey'       => SUPABASE_ANON_KEY,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email'    => $email,
                    'password' => $password,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true) ?? [];

            if (!empty($data['error']) || empty($data['access_token'])) {
                return ['success' => false, 'error' => 'Invalid email or password.'];
            }

            // Guard every key before access
            $accessToken  = $data['access_token']  ?? null;
            $refreshToken = $data['refresh_token']  ?? null;
            $userId       = $data['user']['id']     ?? null;

            if (!$accessToken || !$refreshToken || !$userId) {
                return ['success' => false, 'error' => 'Login failed. Unexpected response from server.'];
            }

            return [
                'success'       => true,
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'user_id'       => $userId,
            ];
        } catch (GuzzleException $e) {
            $this->logError('login', $e);
            return ['success' => false, 'error' => 'Login failed. Please try again.'];
        }
    }

    /**
     * Refresh an expired access token.
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $response = $this->client->post(SUPABASE_AUTH_URL . '/token?grant_type=refresh_token', [
                'headers' => [
                    'apikey'       => SUPABASE_ANON_KEY,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['refresh_token' => $refreshToken],
            ]);

            $data = json_decode($response->getBody()->getContents(), true) ?? [];

            if (!empty($data['error']) || empty($data['access_token'])) {
                return ['success' => false];
            }

            return [
                'success'       => true,
                'access_token'  => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            ];
        } catch (GuzzleException $e) {
            $this->logError('refreshToken', $e);
            return ['success' => false];
        }
    }

    /**
     * Invite a new staff member via Supabase Admin API.
     * Only callable with service role key.
     *
     * MEDIUM-06 note: inviteStaff creates a user without a password, so
     * the staff member cannot log in until they set one via a password-reset
     * email. The calling controller (StaffController) should send a password
     * reset email immediately after a successful invite.
     */
    public function inviteStaff(string $email): array
    {
        try {
            $response = $this->client->post(SUPABASE_AUTH_URL . '/admin/users', [
                'headers' => [
                    'apikey'        => SUPABASE_SERVICE_ROLE_KEY,
                    'Authorization' => 'Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'email'         => $email,
                    'email_confirm' => true,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true) ?? [];

            if (empty($data['id'])) {
                return ['success' => false, 'error' => 'Could not create staff account.'];
            }

            return ['success' => true, 'user_id' => $data['id']];
        } catch (GuzzleException $e) {
            $this->logError('inviteStaff', $e);
            return ['success' => false, 'error' => 'Could not create staff account.'];
        }
    }

    /**
     * Delete a user from Supabase Auth (admin only).
     */
    public function deleteUser(string $userId): bool
    {
        try {
            $this->client->delete(SUPABASE_AUTH_URL . '/admin/users/' . $userId, [
                'headers' => [
                    'apikey'        => SUPABASE_SERVICE_ROLE_KEY,
                    'Authorization' => 'Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                ],
            ]);
            return true;
        } catch (GuzzleException $e) {
            $this->logError('deleteUser', $e);
            return false;
        }
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordReset(string $email): bool
    {
        try {
            $this->client->post(SUPABASE_AUTH_URL . '/recover', [
                'headers' => [
                    'apikey'       => SUPABASE_ANON_KEY,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['email' => $email],
            ]);
            return true;
        } catch (GuzzleException $e) {
            $this->logError('sendPasswordReset', $e);
            return false;
        }
    }

    private function logError(string $method, GuzzleException $e): void
    {
        error_log(
            sprintf("[%s] AuthService::%s error: %s\n", date('Y-m-d H:i:s'), $method, $e->getMessage()),
            3,
            ROOT_PATH . '/storage/logs/app.log'
        );
    }
}
