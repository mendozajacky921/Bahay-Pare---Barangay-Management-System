<?php

declare(strict_types=1);

namespace Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;

class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            \Core\Session::flash('error', 'Please log in to continue.');
            Response::redirect('/login');
        }
    }
}
