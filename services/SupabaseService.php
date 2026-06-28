<?php

declare(strict_types=1);

namespace Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SupabaseService
{
    private Client $client;
    private string $restUrl;
    private string $anonKey;
    private string $serviceKey;

    public function __construct()
    {
        $this->restUrl    = SUPABASE_REST_URL;
        $this->anonKey    = SUPABASE_ANON_KEY;
        $this->serviceKey = SUPABASE_SERVICE_ROLE_KEY;

        $this->client = new Client([
            'timeout'         => 15,
            'connect_timeout' => 5,
        ]);
    }

    /**
     * Build headers for API calls.
     * Use service key for admin operations, user token for user-scoped operations.
     */
    private function headers(bool $useServiceKey = false, ?string $userToken = null): array
    {
        $apiKey = $useServiceKey ? $this->serviceKey : $this->anonKey;

        $headers = [
            'apikey'       => $apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        if ($userToken) {
            $headers['Authorization'] = 'Bearer ' . $userToken;
        } elseif ($useServiceKey) {
            $headers['Authorization'] = 'Bearer ' . $this->serviceKey;
        } else {
            $headers['Authorization'] = 'Bearer ' . $this->anonKey;
        }

        return $headers;
    }

    /**
     * SELECT rows from a table.
     *
     * @param string $table
     * @param array  $filters    e.g. ['status' => 'eq.pending', 'is_active' => 'eq.true']
     * @param string $select     column selector, default '*'
     * @param array  $options    ['order' => 'created_at.desc', 'limit' => 20, 'offset' => 0]
     */
    public function select(
        string $table,
        array $filters = [],
        string $select = '*',
        array $options = [],
        bool $useServiceKey = false,
        ?string $userToken = null
    ): array {
        $query = ['select' => $select];

        foreach ($filters as $col => $val) {
            $query[$col] = $val;
        }

        if (isset($options['order'])) {
            $query['order'] = $options['order'];
        }
        if (isset($options['limit'])) {
            $query['limit'] = $options['limit'];
        }
        if (isset($options['offset'])) {
            $query['offset'] = $options['offset'];
        }

        try {
            $response = $this->client->get($this->restUrl . '/' . $table, [
                'headers' => array_merge(
                    $this->headers($useServiceKey, $userToken),
                    isset($options['count']) ? ['Prefer' => 'count=exact'] : []
                ),
                'query' => $query,
            ]);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            $this->logError('SELECT', $table, $e);
            return [];
        }
    }

    /**
     * SELECT a single row by filter. Returns null if not found.
     */
    public function selectOne(
        string $table,
        array $filters = [],
        string $select = '*',
        bool $useServiceKey = false,
        ?string $userToken = null
    ): ?array {
        $results = $this->select($table, $filters, $select, ['limit' => 1], $useServiceKey, $userToken);
        return $results[0] ?? null;
    }

    /**
     * INSERT a row. Returns the created row or null on failure.
     */
    public function insert(
        string $table,
        array $data,
        bool $useServiceKey = false,
        ?string $userToken = null
    ): ?array {
        try {
            $response = $this->client->post($this->restUrl . '/' . $table, [
                'headers' => array_merge(
                    $this->headers($useServiceKey, $userToken),
                    ['Prefer' => 'return=representation']
                ),
                'json' => $data,
            ]);

            $rows = json_decode($response->getBody()->getContents(), true) ?? [];
            return $rows[0] ?? null;
        } catch (GuzzleException $e) {
            $this->logError('INSERT', $table, $e);
            return null;
        }
    }

    /**
     * UPDATE rows matching filters. Returns updated rows or empty array.
     */
    public function update(
        string $table,
        array $data,
        array $filters,
        bool $useServiceKey = false,
        ?string $userToken = null
    ): array {
        $query = [];
        foreach ($filters as $col => $val) {
            $query[$col] = $val;
        }

        try {
            $response = $this->client->patch($this->restUrl . '/' . $table, [
                'headers' => array_merge(
                    $this->headers($useServiceKey, $userToken),
                    ['Prefer' => 'return=representation']
                ),
                'query' => $query,
                'json'  => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            $this->logError('UPDATE', $table, $e);
            return [];
        }
    }

    /**
     * DELETE rows matching filters.
     */
    public function delete(
        string $table,
        array $filters,
        bool $useServiceKey = false,
        ?string $userToken = null
    ): bool {
        $query = [];
        foreach ($filters as $col => $val) {
            $query[$col] = $val;
        }

        try {
            $this->client->delete($this->restUrl . '/' . $table, [
                'headers' => $this->headers($useServiceKey, $userToken),
                'query'   => $query,
            ]);
            return true;
        } catch (GuzzleException $e) {
            $this->logError('DELETE', $table, $e);
            return false;
        }
    }

    /**
     * Execute a Supabase RPC function.
     */
    public function rpc(
        string $function,
        array $params = [],
        bool $useServiceKey = false,
        ?string $userToken = null
    ): mixed {
        try {
            $response = $this->client->post(SUPABASE_URL . '/rest/v1/rpc/' . $function, [
                'headers' => $this->headers($useServiceKey, $userToken),
                'json'    => $params,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $this->logError('RPC', $function, $e);
            return null;
        }
    }

    /**
     * Count rows matching filters.
     *
     * CRITICAL-07 fix: The original code used array destructuring on explode():
     *   [, $total] = explode('/', $contentRange) + ['', '0'];
     * This is broken — the `+` operator on arrays uses numeric keys, so the
     * fallback values at keys 0 and 1 only fill in when those keys are MISSING.
     * Since explode always returns key 0 (and key 1 when '/' exists), the fallback
     * '0' at key 1 was never used, meaning a missing '/' in the header produced an
     * undefined offset notice and silently returned 0 for valid counts too.
     *
     * Fixed by: splitting safely, checking parts count, and parsing explicitly.
     */
    public function count(string $table, array $filters = [], bool $useServiceKey = false): int
    {
        $query = ['select' => 'id'];
        foreach ($filters as $col => $val) {
            $query[$col] = $val;
        }

        try {
            $response = $this->client->get($this->restUrl . '/' . $table, [
                'headers' => array_merge(
                    $this->headers($useServiceKey),
                    ['Prefer' => 'count=exact']
                ),
                'query' => $query,
            ]);

            // Content-Range header format from PostgREST: "0-14/47" or "*/47"
            $contentRange = $response->getHeader('Content-Range')[0] ?? '';
            if ($contentRange === '' || !str_contains($contentRange, '/')) {
                return 0;
            }

            $parts = explode('/', $contentRange, 2);
            $total = $parts[1] ?? '0';

            return is_numeric($total) ? (int)$total : 0;
        } catch (GuzzleException $e) {
            $this->logError('COUNT', $table, $e);
            return 0;
        }
    }

    private function logError(string $operation, string $target, GuzzleException $e): void
    {
        $message = sprintf(
            "[%s] Supabase %s on '%s' failed: %s\n",
            date('Y-m-d H:i:s'),
            $operation,
            $target,
            $e->getMessage()
        );
        error_log($message, 3, ROOT_PATH . '/storage/logs/app.log');
    }
}
