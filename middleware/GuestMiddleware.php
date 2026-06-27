<?php

declare(strict_types=1);

namespace Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;

class GuestMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            $role = Auth::role();
            $redirect = match ($role) {
                'resident'                        => '/resident/dashboard',
                'captain', 'secretary', 'clerk'  => '/staff/dashboard',
                default                           => '/',
            };
            Response::redirect($redirect);
        }
    }
}
