<?php
declare(strict_types=1);
namespace Controllers\Staff;
use Core\Controller; use Core\Request;
class StaffController extends Controller {{
    public function index(Request $r): void {{ $this->render('staff/dashboard', ['pageTitle' => 'Staff']); }}
    public function show(Request $r, array $p): void {{ $this->render('staff/dashboard', ['pageTitle' => 'Detail']); }}
    public function store(Request $r): void {{ $this->redirect('/staff/dashboard'); }}
    public function edit(Request $r, array $p): void {{ $this->render('staff/dashboard', ['pageTitle' => 'Edit']); }}
    public function update(Request $r, array $p): void {{ $this->redirect('/staff/dashboard'); }}
    public function delete(Request $r, array $p): void {{ $this->redirect('/staff/dashboard'); }}
    public function verify(Request $r, array $p): void {{ $this->redirect('/staff/residents'); }}
    public function deactivate(Request $r, array $p): void {{ $this->redirect('/staff/staff-accounts'); }}
    public function updateStatus(Request $r, array $p): void {{ $this->redirect('/staff/requests'); }}
    public function generateDocument(Request $r, array $p): void {{ $this->redirect('/staff/requests'); }}
    public function recordWalkIn(Request $r, array $p): void {{ $this->redirect('/staff/requests'); }}
}}
