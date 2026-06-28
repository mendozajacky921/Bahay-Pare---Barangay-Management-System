<?php

declare(strict_types=1);

namespace Controllers\Staff\Concerns;

use Core\Request;

trait PlaceholderActions
{
    public function index(Request $request): void
    {
        $this->render('staff/dashboard', ['pageTitle' => 'Staff']);
    }

    public function show(Request $request, array $params): void
    {
        $this->render('staff/dashboard', ['pageTitle' => 'Detail']);
    }

    public function store(Request $request): void
    {
        $this->redirect('/staff/dashboard');
    }

    public function edit(Request $request, array $params): void
    {
        $this->render('staff/dashboard', ['pageTitle' => 'Edit']);
    }

    public function update(Request $request, array $params): void
    {
        $this->redirect('/staff/dashboard');
    }

    public function delete(Request $request, array $params): void
    {
        $this->redirect('/staff/dashboard');
    }

    public function verify(Request $request, array $params): void
    {
        $this->redirect('/staff/residents');
    }

    public function deactivate(Request $request, array $params): void
    {
        $this->redirect('/staff/staff-accounts');
    }

    public function updateStatus(Request $request, array $params): void
    {
        $this->redirect('/staff/requests');
    }

    public function generateDocument(Request $request, array $params): void
    {
        $this->redirect('/staff/requests');
    }

    public function recordWalkIn(Request $request, array $params): void
    {
        $this->redirect('/staff/requests');
    }
}