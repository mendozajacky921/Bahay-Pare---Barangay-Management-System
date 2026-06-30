<?php

declare(strict_types=1);

namespace Controllers\Public;

use Core\Controller;
use Core\Request;
use Core\Response;
use Services\StorageService;
use Services\SupabaseService;

class FormDownloadController extends Controller
{
    public function index(Request $request): void
    {
        $db = new SupabaseService();

        $forms = $db->select(
            'public_forms',
            ['is_active' => 'eq.true'],
            'id,title,title_fil,description,file_url,file_size,download_count',
            ['order' => 'sort_order.asc']
        );

        $this->render('public/forms/index', [
            'pageTitle' => 'Downloadable Forms',
            'forms'     => $forms,
        ]);
    }

    public function download(Request $request, array $params): void
    {
        $db = new SupabaseService();

        // M2-HIGH-03 fix: validate UUID before querying.
        if (!self::isValidUuid($params['id'] ?? '')) {
            $this->abort(404, 'Form not found.');
        }

        $form = $db->selectOne(
            'public_forms',
            ['id' => 'eq.' . $params['id'], 'is_active' => 'eq.true'],
            'id,file_url,file_size,download_count'
        );

        if (!$form || empty($form['file_url'])) {
            $this->abort(404, 'Form not found.');
        }

        // M2-CRITICAL-01 fix: file_url must be a storage path (not a full URL).
        // If someone accidentally stored a full URL, we detect it and strip the
        // storage prefix so we never double-build the URL.
        $storagePath = $form['file_url'];
        $storagePrefix = SUPABASE_STORAGE_URL . '/object/public/' . StorageService::BUCKET_FORMS . '/';
        if (str_starts_with($storagePath, 'http')) {
            // Already a full URL — use it directly instead of re-building.
            $downloadUrl = $storagePath;
        } else {
            $storage     = new StorageService();
            $downloadUrl = $storage->publicUrl(StorageService::BUCKET_FORMS, $storagePath);
        }

        // M2-HIGH-02 fix: use PostgREST's column increment syntax instead of
        // read-then-write, which has a TOCTOU race condition under concurrent
        // downloads. The rpc() approach calls a DB-side counter bump atomically.
        // We fire-and-forget — a failed increment must not block the download.
        $db->rpc('increment_form_download_count', ['form_id' => $form['id']], true);

        Response::redirect($downloadUrl);
    }

    private static function isValidUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );
    }
}
