<?php
declare(strict_types=1);
namespace Controllers\Resident;
use Core\Controller; use Core\Request;
class DashboardController extends Controller {
    public function index(Request $request): void { $this->render('resident/dashboard', ['pageTitle' => 'My Dashboard']); }
}
