<?php

declare(strict_types=1);

namespace Controllers\Public;

use Core\Controller;
use Core\Request;
use Services\SupabaseService;

class HotlineController extends Controller
{
    public function index(Request $request): void
    {
        $db = new SupabaseService();

        $hotlines = $db->select(
            'hotlines',
            ['is_active' => 'eq.true'],
            'id,name,name_fil,category,phone_number,alt_number,sort_order',
            ['order' => 'category.asc,sort_order.asc']
        );

        // Group by category for display
        $grouped = [];
        foreach ($hotlines as $hotline) {
            $category = $hotline['category'] ?: 'General';
            $grouped[$category][] = $hotline;
        }

        $this->render('public/hotlines/index', [
            'pageTitle'      => 'Emergency Hotlines',
            'groupedHotlines' => $grouped,
        ]);
    }
}
