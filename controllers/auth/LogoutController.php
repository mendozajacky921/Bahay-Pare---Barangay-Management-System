<?php
declare(strict_types=1);
namespace Controllers\Auth;
use Core\Controller; use Core\Request; use Core\Auth;
class LogoutController extends Controller {
    public function store(Request $request): void { Auth::logout(); $this->redirect('/login'); }
}
