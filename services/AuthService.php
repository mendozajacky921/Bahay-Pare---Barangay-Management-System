<?php

declare(strict_types=1);

namespace Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AuthService
{
    private Client $client;
    private SupabaseService $db;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 15]);
        $this->db     = new SupabaseService();
    }

    /**
     * Register a new user via Supabase Auth.
     * Returns ['success' => true, 'user' => [...]] or ['success' => false, 'error' => '...']
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

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['error'])) {
                return ['success' => false, 'error' => $data['error']['message'] ?? 'Registration failed.'];
            }

            return ['success' => true, 'user' => $data];
        } catch (GuzzleException $e) {
            $this->logError($e);
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * Sign in with email and password.
     * Returns ['success' => true, 'access_token' => '...', 'refresh_token' => '...', 'user_id' => '...']
     * or ['success' => false, 'error' => '...']
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

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['error'])) {
                return ['success' => false, 'error' => 'Invalid email or password.'];
            }

            return [
                'success'       => true,
                'access_token'  => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'user_id'       => $data['user']['id'],
            ];
        } catch (GuzzleException $e) {
            $this->logError($e);
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

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['error'])) {
                return ['success' => false];
            }

            return [
                'success'       => true,
                'access_token'  => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
            ];
        } catch (GuzzleException $e) {
            $this->logError($e);
            return ['success' => false];
        }
    }

    /**
     * Invite a new staff member via Supabase Admin API.
     * Only callable with service role key.
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

            $data = json_decode($response->getBody()->getContents(), true);

            return ['success' => true, 'user_id' => $data['id']];
        } catch (GuzzleException $e) {
            $this->logError($e);
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
            $this->logError($e);
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
            $this->logError($e);
            return false;
        }
    }

    private function logError(GuzzleException $e): void
    {
        error_log(
            sprintf("[%s] AuthService error: %s\n", date('Y-m-d H:i:s'), $e->getMessage()),
            3,
            ROOT_PATH . '/storage/logs/app.log'
        );
    }
}
