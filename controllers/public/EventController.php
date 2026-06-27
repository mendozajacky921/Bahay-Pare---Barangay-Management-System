<?php
declare(strict_types=1);
namespace Controllers\Public;
use Core\Controller; use Core\Request;
class EventController extends Controller {
    public function index(Request $request): void  { $this->render('public/' . strtolower(str_replace('Controller','',str_replace('Download','s',basename(__FILE__,'.php')))), ['pageTitle' => 'Coming Soon']); }
    public function show(Request $request, array $params): void { $this->render('public/home', ['pageTitle' => 'Item']); }
    public function download(Request $request, array $params): void { $this->redirect('/forms'); }
}
