<?php

declare(strict_types=1);

namespace Controllers\Public;

use Core\Controller;
use Core\Request;
use Services\SupabaseService;

class ProjectController extends Controller
{
    private const SELECT = 'id,title,title_fil,description,description_fil,status,budget,start_date,end_date,image_url';

    public function index(Request $request): void
    {
        $db      = new SupabaseService();
        $page    = max(1, $request->integer('page', 1));
        $perPage = ITEMS_PER_PAGE;

        $status  = $request->get('status', '');
        $filters = ['is_published' => 'eq.true'];

        if (in_array($status, ['planned', 'ongoing', 'completed'], true)) {
            $filters['status'] = 'eq.' . $status;
        }

        $total      = $db->count('projects', $filters);
        $totalPages = (int) ceil(max($total, 1) / $perPage);
        $page       = min($page, $totalPages);
        $offset     = ($page - 1) * $perPage;

        $projects = $db->select(
            'projects',
            $filters,
            self::SELECT,
            ['order' => 'start_date.desc', 'limit' => $perPage, 'offset' => $offset]
        );

        $this->render('public/projects/index', [
            'pageTitle'      => 'Projects',
            'projects'       => $projects,
            'currentPage'    => $page,
            'totalPages'     => $totalPages,
            'selectedStatus' => $status,
        ]);
    }

    public function show(Request $request, array $params): void
    {
        $db = new SupabaseService();

        // M2-HIGH-03 fix: validate UUID before hitting Supabase.
        if (!self::isValidUuid($params['id'] ?? '')) {
            $this->abort(404, 'Project not found.');
        }

        $project = $db->selectOne(
            'projects',
            ['id' => 'eq.' . $params['id'], 'is_published' => 'eq.true'],
            self::SELECT
        );

        if (!$project) {
            $this->abort(404, 'Project not found.');
        }

        $this->render('public/projects/show', [
            'pageTitle' => $project['title'],
            'project'   => $project,
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
