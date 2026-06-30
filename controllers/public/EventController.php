<?php

declare(strict_types=1);

namespace Controllers\Public;

use Core\Controller;
use Core\Request;
use Services\SupabaseService;

class EventController extends Controller
{
    private const SELECT = 'id,title,title_fil,description,description_fil,location,event_date,end_date,image_url';

    public function index(Request $request): void
    {
        $db      = new SupabaseService();
        $page    = max(1, $request->integer('page', 1));
        $perPage = ITEMS_PER_PAGE;

        // M2-MEDIUM-01 fix: use UTC so the gte filter matches Supabase's
        // stored timestamps correctly regardless of server timezone.
        $nowUtc  = gmdate('Y-m-d\TH:i:s\Z');
        $filters = [
            'is_published' => 'eq.true',
            'event_date'   => 'gte.' . $nowUtc,
        ];

        $total = $db->count('events', $filters);

        // M2-CRITICAL-02 fix: if there are no upcoming events at all (total=0),
        // fall back to past events BEFORE calculating offset or totalPages.
        // Previously $offset was derived from the upcoming $total, then $total
        // was reassigned inside the fallback — but $totalPages was still
        // calculated from the stale (0) upcoming total, pinning it at 1
        // regardless of how many past events existed.
        $showingPast = false;
        if ($total === 0) {
            $filters     = ['is_published' => 'eq.true'];
            $total       = $db->count('events', $filters);
            $showingPast = true;
        }

        // Now that $total is final, derive offset and totalPages correctly.
        $totalPages = (int) ceil(max($total, 1) / $perPage);
        $page       = min($page, $totalPages); // clamp so out-of-range pages don't return empty
        $offset     = ($page - 1) * $perPage;

        $events = $db->select(
            'events',
            $filters,
            self::SELECT,
            [
                'order'  => $showingPast ? 'event_date.desc' : 'event_date.asc',
                'limit'  => $perPage,
                'offset' => $offset,
            ]
        );

        $this->render('public/events/index', [
            'pageTitle'   => 'Events',
            'events'      => $events,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'showingPast' => $showingPast,
        ]);
    }

    public function show(Request $request, array $params): void
    {
        $db = new SupabaseService();

        // M2-HIGH-03 fix: validate UUID format before hitting Supabase.
        // A malformed ID would generate a noisy 400 from PostgREST instead
        // of a clean 404. UUIDs are 8-4-4-4-12 hex characters.
        if (!self::isValidUuid($params['id'] ?? '')) {
            $this->abort(404, 'Event not found.');
        }

        $event = $db->selectOne(
            'events',
            ['id' => 'eq.' . $params['id'], 'is_published' => 'eq.true'],
            self::SELECT
        );

        if (!$event) {
            $this->abort(404, 'Event not found.');
        }

        $this->render('public/events/show', [
            'pageTitle' => $event['title'],
            'event'     => $event,
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
