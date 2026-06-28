<?php

declare(strict_types=1);

namespace Controllers\Public;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

class LocaleController extends Controller
{
    /**
     * CRITICAL-05 fix: views/layouts/public.php posts to /set-locale on every
     * language-toggle click, but no such route existed — every click 404'd.
     * This controller + the route registered in index.php closes that gap.
     */
    public function store(Request $request): void
    {
        $locale = $request->post('locale', '');

        if (in_array($locale, SUPPORTED_LOCALES, true)) {
            Session::set('locale', $locale);
        }

        Response::redirectBack('/');
    }
}
