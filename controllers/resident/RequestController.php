<?php
declare(strict_types=1);
namespace Controllers\Resident;
use Core\Controller; use Core\Request;
class RequestController extends Controller {
    public function index(Request $request): void  { $this->render('resident/requests/index', ['pageTitle' => 'My Requests']); }
    public function create(Request $request): void { $this->render('resident/requests/create', ['pageTitle' => 'New Request']); }
    public function store(Request $request): void  { $this->redirect('/resident/requests'); }
    public function show(Request $request, array $params): void { $this->render('resident/requests/index', ['pageTitle' => 'Request Detail']); }
}
