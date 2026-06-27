<?php
declare(strict_types=1);
namespace Controllers\Resident;
use Core\Controller; use Core\Request;
class ProfileController extends Controller {
    public function show(Request $request): void   { $this->render('resident/dashboard', ['pageTitle' => 'My Profile']); }
    public function update(Request $request): void { $this->redirect('/resident/profile'); }
}
