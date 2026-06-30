<?php

declare(strict_types=1);

namespace Controllers\Public;

use Core\Controller;
use Core\Request;
use Services\SupabaseService;

class AnnouncementController extends Controller
{
    private const SELECT = 'id,title,title_fil,content,content_fil,image_url,published_at';

    public function index(Request $request): void
    {
        $db      = new SupabaseService();
        $page    = max(1, $request->integer('page', 1));
        $perPage = ITEMS_PER_PAGE;

        $filters    = ['is_published' => 'eq.true'];
        $total      = $db->count('announcements', $filters);
        $totalPages = (int) ceil(max($total, 1) / $perPage);
        $page       = min($page, $totalPages);
        $offset     = ($page - 1) * $perPage;

        $announcements = $db->select(
            'announcements',
            $filters,
            self::SELECT,
            ['order' => 'published_at.desc', 'limit' => $perPage, 'offset' => $offset]
        );

        $this->render('public/announcements/index', [
            'pageTitle'     => 'Announcements',
            'announcements' => $announcements,
            'currentPage'   => $page,
            'totalPages'    => $totalPages,
        ]);
    }

    public function show(Request $request, array $params): void
    {
        $db = new SupabaseService();

        // M2-HIGH-03 fix: validate UUID before hitting Supabase to avoid
        // noisy 400 errors from PostgREST on malformed IDs.
        if (!self::isValidUuid($params['id'] ?? '')) {
            $this->abort(404, 'Announcement not found.');
        }

        $announcement = $db->selectOne(
            'announcements',
            ['id' => 'eq.' . $params['id'], 'is_published' => 'eq.true'],
            self::SELECT
        );

        if (!$announcement) {
            $this->abort(404, 'Announcement not found.');
        }

        $this->render('public/announcements/show', [
            'pageTitle'    => $announcement['title'],
            'announcement' => $announcement,
        ]);
    }

    private static function isValidUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );
    }
}
