<?php
declare(strict_types=1);
namespace Controllers\Auth;
use Core\Controller; use Core\Request;
class RegisterController extends Controller {
    public function show(Request $request): void       { $this->render('auth/register', ['pageTitle' => 'Register']); }
    public function store(Request $request): void      { $this->redirect('/register'); }
    public function verifyEmail(Request $request): void { $this->redirect('/login'); }
}
