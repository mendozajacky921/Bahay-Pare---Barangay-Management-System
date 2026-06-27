<?php

declare(strict_types=1);

namespace Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class StorageService
{
    private Client $client;

    // Bucket names
    public const BUCKET_IDS        = 'resident-ids';
    public const BUCKET_DOCUMENTS  = 'generated-documents';
    public const BUCKET_IMAGES     = 'public-images';
    public const BUCKET_FORMS      = 'public-forms';

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
    }

    /**
     * Upload a file to a Supabase Storage bucket.
     *
     * @param string $bucket    Bucket name
     * @param string $path      Path within bucket, e.g. 'residents/uuid/id-photo.jpg'
     * @param string $filePath  Local file path
     * @param string $mimeType  MIME type of the file
     * @param bool   $public    Whether the file should be public
     */
    public function upload(
        string $bucket,
        string $path,
        string $filePath,
        string $mimeType,
        bool $public = false
    ): ?string {
        if (!file_exists($filePath)) {
            return null;
        }

        $url = SUPABASE_STORAGE_URL . '/object/' . $bucket . '/' . $path;

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'apikey'        => SUPABASE_SERVICE_ROLE_KEY,
                    'Authorization' => 'Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                    'Content-Type'  => $mimeType,
                    'x-upsert'      => 'true',
                ],
                'body' => fopen($filePath, 'r'),
            ]);

            if ($response->getStatusCode() === 200) {
                return $public
                    ? $this->publicUrl($bucket, $path)
                    : $path;
            }

            return null;
        } catch (GuzzleException $e) {
            $this->logError('upload', $e);
            return null;
        }
    }

    /**
     * Upload raw string content (e.g. generated PDF bytes).
     */
    public function uploadContent(
        string $bucket,
        string $path,
        string $content,
        string $mimeType = 'application/pdf'
    ): ?string {
        $url = SUPABASE_STORAGE_URL . '/object/' . $bucket . '/' . $path;

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'apikey'        => SUPABASE_SERVICE_ROLE_KEY,
                    'Authorization' => 'Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                    'Content-Type'  => $mimeType,
                    'x-upsert'      => 'true',
                ],
                'body' => $content,
            ]);

            return $response->getStatusCode() === 200 ? $path : null;
        } catch (GuzzleException $e) {
            $this->logError('uploadContent', $e);
            return null;
        }
    }

    /**
     * Generate a signed URL for private file access (expires after N seconds).
     */
    public function signedUrl(string $bucket, string $path, int $expiresIn = 3600): ?string
    {
        $url = SUPABASE_STORAGE_URL . '/object/sign/' . $bucket . '/' . $path;

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'apikey'        => SUPABASE_SERVICE_ROLE_KEY,
                    'Authorization' => 'Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                    'Content-Type'  => 'application/json',
                ],
                'json' => ['expiresIn' => $expiresIn],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return isset($data['signedURL']) ? SUPABASE_URL . $data['signedURL'] : null;
        } catch (GuzzleException $e) {
            $this->logError('signedUrl', $e);
            return null;
        }
    }

    /**
     * Get the public URL for a public bucket file.
     */
    public function publicUrl(string $bucket, string $path): string
    {
        return SUPABASE_STORAGE_URL . '/object/public/' . $bucket . '/' . $path;
    }

    /**
     * Delete a file from storage.
     */
    public function delete(string $bucket, string $path): bool
    {
        try {
            $this->client->delete(SUPABASE_STORAGE_URL . '/object/' . $bucket . '/' . $path, [
                'headers' => [
                    'apikey'        => SUPABASE_SERVICE_ROLE_KEY,
                    'Authorization' => 'Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                ],
            ]);
            return true;
        } catch (GuzzleException $e) {
            $this->logError('delete', $e);
            return false;
        }
    }

    /**
     * Validate an uploaded file before sending to storage.
     */
    public function validateUpload(array $file, array $allowedTypes, int $maxMb = 5): array
    {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = match ($file['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
                UPLOAD_ERR_PARTIAL  => 'File upload was interrupted.',
                UPLOAD_ERR_NO_FILE  => 'No file was uploaded.',
                default             => 'File upload failed.',
            };
            return $errors;
        }

        // Validate MIME type from actual file content (not just extension)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $errors[] = 'File type not allowed. Accepted: ' . implode(', ', $allowedTypes);
        }

        $sizeMb = $file['size'] / (1024 * 1024);
        if ($sizeMb > $maxMb) {
            $errors[] = "File must not exceed {$maxMb}MB.";
        }

        return $errors;
    }

    private function logError(string $operation, GuzzleException $e): void
    {
        error_log(
            sprintf("[%s] StorageService::%s error: %s\n", date('Y-m-d H:i:s'), $operation, $e->getMessage()),
            3,
            ROOT_PATH . '/storage/logs/app.log'
        );
    }
}
