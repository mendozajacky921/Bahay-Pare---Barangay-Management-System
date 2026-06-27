<?php

declare(strict_types=1);

namespace Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        if (!Auth::hasRole(...$this->allowedRoles)) {
            Response::abort(403, 'You do not have permission to access this page.');
        }
    }
}
