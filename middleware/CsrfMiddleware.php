<?php

declare(strict_types=1);

namespace Middleware;

use Core\Request;
use Core\Response;
use Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if (!$request->isPost()) {
            return;
        }

        $token = $request->post('_csrf_token', '');

        if (!Session::validateCsrfToken($token)) {
            // Rotate token after failed attempt
            Session::rotateCsrfToken();
            Session::flash('error', 'Your session has expired. Please try again.');
            Response::redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }

        // Rotate after successful use
        Session::rotateCsrfToken();
    }
}
