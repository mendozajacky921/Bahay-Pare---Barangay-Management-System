<?php
declare(strict_types=1);
namespace Controllers\Auth;
use Core\Controller; use Core\Request;
class LoginController extends Controller {
    public function show(Request $request): void { $this->render('auth/login', ['pageTitle' => 'Log In']); }
    public function store(Request $request): void { $this->redirect('/login'); }
}
