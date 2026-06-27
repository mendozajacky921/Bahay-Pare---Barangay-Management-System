<?php

declare(strict_types=1);

namespace Controllers\Public;

use Core\Controller;
use Core\Request;
use Core\View;
use Services\SupabaseService;

class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $db = new SupabaseService();

        $announcements = $db->select(
            'announcements',
            ['is_published' => 'eq.true'],
            'id,title,title_fil,content,content_fil,image_url,published_at',
            ['order' => 'published_at.desc', 'limit' => 3]
        );

        $this->render('public/home', [
            'pageTitle'     => 'Home',
            'announcements' => $announcements,
        ]);
    }
}
