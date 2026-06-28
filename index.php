<?php

declare(strict_types=1);

define('ROOT_PATH', __DIR__);
define('START_TIME', microtime(true));

// ── Autoloader ────────────────────────────────────────────
require_once ROOT_PATH . '/vendor/autoload.php';

// ── Environment ───────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();
$dotenv->required([
    'APP_SECRET',
    'SUPABASE_URL',
    'SUPABASE_ANON_KEY',
    'SUPABASE_SERVICE_ROLE_KEY',
]);

// ── Config ────────────────────────────────────────────────
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/supabase.php';
require_once ROOT_PATH . '/config/mail.php';
require_once ROOT_PATH . '/config/payment.php';

// ── Core ──────────────────────────────────────────────────
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Request.php';
require_once ROOT_PATH . '/core/Response.php';
require_once ROOT_PATH . '/core/View.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Middleware.php';
require_once ROOT_PATH . '/core/Router.php';

// ── Middleware ────────────────────────────────────────────
require_once ROOT_PATH . '/middleware/AuthMiddleware.php';
require_once ROOT_PATH . '/middleware/RoleMiddleware.php';
require_once ROOT_PATH . '/middleware/GuestMiddleware.php';
require_once ROOT_PATH . '/middleware/CsrfMiddleware.php';

// ── Services ──────────────────────────────────────────────
require_once ROOT_PATH . '/services/SupabaseService.php';
require_once ROOT_PATH . '/services/AuthService.php';
require_once ROOT_PATH . '/services/StorageService.php';
require_once ROOT_PATH . '/services/EmailService.php';
require_once ROOT_PATH . '/services/PdfService.php';

// ── Controllers ───────────────────────────────────────────
// Public
require_once ROOT_PATH . '/controllers/public/HomeController.php';
require_once ROOT_PATH . '/controllers/public/AnnouncementController.php';
require_once ROOT_PATH . '/controllers/public/ProjectController.php';
require_once ROOT_PATH . '/controllers/public/EventController.php';
require_once ROOT_PATH . '/controllers/public/HotlineController.php';
require_once ROOT_PATH . '/controllers/public/FormDownloadController.php';
require_once ROOT_PATH . '/controllers/public/LocaleController.php';
// Auth
require_once ROOT_PATH . '/controllers/auth/RegisterController.php';
require_once ROOT_PATH . '/controllers/auth/LoginController.php';
require_once ROOT_PATH . '/controllers/auth/LogoutController.php';
// Resident
require_once ROOT_PATH . '/controllers/resident/DashboardController.php';
require_once ROOT_PATH . '/controllers/resident/RequestController.php';
require_once ROOT_PATH . '/controllers/resident/ProfileController.php';
// Staff
require_once ROOT_PATH . '/controllers/staff/DashboardController.php';
require_once ROOT_PATH . '/controllers/staff/RequestController.php';
require_once ROOT_PATH . '/controllers/staff/ResidentController.php';
require_once ROOT_PATH . '/controllers/staff/AnnouncementController.php';
require_once ROOT_PATH . '/controllers/staff/ProjectController.php';
require_once ROOT_PATH . '/controllers/staff/EventController.php';
require_once ROOT_PATH . '/controllers/staff/HotlineController.php';
require_once ROOT_PATH . '/controllers/staff/FormController.php';
require_once ROOT_PATH . '/controllers/staff/StaffController.php';
require_once ROOT_PATH . '/controllers/staff/SettingsController.php';
require_once ROOT_PATH . '/controllers/staff/ReportController.php';
require_once ROOT_PATH . '/controllers/staff/PaymentController.php';

// ── Bootstrap ─────────────────────────────────────────────
Core\Session::start();

$router = new Core\Router();

// ── Public Routes ─────────────────────────────────────────
$router->get('/', 'Controllers\Public\HomeController@index');
$router->get('/announcements', 'Controllers\Public\AnnouncementController@index');
$router->get('/announcements/{id}', 'Controllers\Public\AnnouncementController@show');
$router->get('/projects', 'Controllers\Public\ProjectController@index');
$router->get('/projects/{id}', 'Controllers\Public\ProjectController@show');
$router->get('/events', 'Controllers\Public\EventController@index');
$router->get('/events/{id}', 'Controllers\Public\EventController@show');
$router->get('/hotlines', 'Controllers\Public\HotlineController@index');
$router->get('/forms', 'Controllers\Public\FormDownloadController@index');
$router->get('/forms/{id}/download', 'Controllers\Public\FormDownloadController@download');
$router->get('/privacy-policy', fn() => Core\View::render('public/privacy-policy'));
$router->post('/set-locale', 'Controllers\Public\LocaleController@store', ['csrf']);

// ── Auth Routes (guest only) ──────────────────────────────
$router->get('/register', 'Controllers\Auth\RegisterController@show', ['guest']);
$router->post('/register', 'Controllers\Auth\RegisterController@store', ['guest']);
$router->get('/login', 'Controllers\Auth\LoginController@show', ['guest']);
$router->post('/login', 'Controllers\Auth\LoginController@store', ['guest']);
$router->post('/logout', 'Controllers\Auth\LogoutController@store', ['auth']);
$router->get('/verify-email', 'Controllers\Auth\RegisterController@verifyEmail');

// ── Resident Routes (auth + resident role) ────────────────
$router->get('/resident/dashboard', 'Controllers\Resident\DashboardController@index', ['auth', 'role:resident,captain,secretary,clerk']);
$router->get('/resident/requests', 'Controllers\Resident\RequestController@index', ['auth', 'role:resident']);
$router->get('/resident/requests/new', 'Controllers\Resident\RequestController@create', ['auth', 'role:resident']);
$router->post('/resident/requests', 'Controllers\Resident\RequestController@store', ['auth', 'role:resident', 'csrf']);
$router->get('/resident/requests/{id}', 'Controllers\Resident\RequestController@show', ['auth', 'role:resident']);
$router->get('/resident/profile', 'Controllers\Resident\ProfileController@show', ['auth', 'role:resident']);
$router->post('/resident/profile', 'Controllers\Resident\ProfileController@update', ['auth', 'role:resident', 'csrf']);

// ── Staff Routes (auth + staff role) ─────────────────────
$staffRoles = 'role:captain,secretary,clerk';
$router->get('/staff/dashboard', 'Controllers\Staff\DashboardController@index', ['auth', $staffRoles]);
$router->get('/staff/requests', 'Controllers\Staff\RequestController@index', ['auth', $staffRoles]);
$router->get('/staff/requests/{id}', 'Controllers\Staff\RequestController@show', ['auth', $staffRoles]);
$router->post('/staff/requests/{id}/status', 'Controllers\Staff\RequestController@updateStatus', ['auth', $staffRoles, 'csrf']);
$router->post('/staff/requests/{id}/document', 'Controllers\Staff\RequestController@generateDocument', ['auth', $staffRoles, 'csrf']);
$router->post('/staff/requests/{id}/payment', 'Controllers\Staff\PaymentController@recordWalkIn', ['auth', $staffRoles, 'csrf']);
$router->get('/staff/residents', 'Controllers\Staff\ResidentController@index', ['auth', $staffRoles]);
$router->get('/staff/residents/{id}', 'Controllers\Staff\ResidentController@show', ['auth', $staffRoles]);
$router->post('/staff/residents/{id}/verify', 'Controllers\Staff\ResidentController@verify', ['auth', $staffRoles, 'csrf']);
$router->post('/staff/residents/{id}/update', 'Controllers\Staff\ResidentController@update', ['auth', $staffRoles, 'csrf']);
$router->get('/staff/announcements', 'Controllers\Staff\AnnouncementController@index', ['auth', $staffRoles]);
$router->post('/staff/announcements', 'Controllers\Staff\AnnouncementController@store', ['auth', 'role:captain,secretary', 'csrf']);
$router->get('/staff/announcements/{id}/edit', 'Controllers\Staff\AnnouncementController@edit', ['auth', 'role:captain,secretary']);
$router->post('/staff/announcements/{id}/update', 'Controllers\Staff\AnnouncementController@update', ['auth', 'role:captain,secretary', 'csrf']);
$router->post('/staff/announcements/{id}/delete', 'Controllers\Staff\AnnouncementController@delete', ['auth', 'role:captain,secretary', 'csrf']);
$router->get('/staff/projects', 'Controllers\Staff\ProjectController@index', ['auth', $staffRoles]);
$router->post('/staff/projects', 'Controllers\Staff\ProjectController@store', ['auth', 'role:captain,secretary', 'csrf']);
$router->get('/staff/projects/{id}/edit', 'Controllers\Staff\ProjectController@edit', ['auth', 'role:captain,secretary']);
$router->post('/staff/projects/{id}/update', 'Controllers\Staff\ProjectController@update', ['auth', 'role:captain,secretary', 'csrf']);
$router->post('/staff/projects/{id}/delete', 'Controllers\Staff\ProjectController@delete', ['auth', 'role:captain,secretary', 'csrf']);
$router->get('/staff/events', 'Controllers\Staff\EventController@index', ['auth', $staffRoles]);
$router->post('/staff/events', 'Controllers\Staff\EventController@store', ['auth', 'role:captain,secretary', 'csrf']);
$router->get('/staff/events/{id}/edit', 'Controllers\Staff\EventController@edit', ['auth', 'role:captain,secretary']);
$router->post('/staff/events/{id}/update', 'Controllers\Staff\EventController@update', ['auth', 'role:captain,secretary', 'csrf']);
$router->post('/staff/events/{id}/delete', 'Controllers\Staff\EventController@delete', ['auth', 'role:captain,secretary', 'csrf']);
$router->get('/staff/hotlines', 'Controllers\Staff\HotlineController@index', ['auth', $staffRoles]);
$router->post('/staff/hotlines', 'Controllers\Staff\HotlineController@store', ['auth', 'role:captain,secretary', 'csrf']);
$router->post('/staff/hotlines/{id}/update', 'Controllers\Staff\HotlineController@update', ['auth', 'role:captain,secretary', 'csrf']);
$router->post('/staff/hotlines/{id}/delete', 'Controllers\Staff\HotlineController@delete', ['auth', 'role:captain,secretary', 'csrf']);
$router->get('/staff/forms', 'Controllers\Staff\FormController@index', ['auth', $staffRoles]);
$router->post('/staff/forms', 'Controllers\Staff\FormController@store', ['auth', 'role:captain,secretary', 'csrf']);
$router->post('/staff/forms/{id}/delete', 'Controllers\Staff\FormController@delete', ['auth', 'role:captain,secretary', 'csrf']);

// Captain-only routes
$router->get('/staff/staff-accounts', 'Controllers\Staff\StaffController@index', ['auth', 'role:captain']);
$router->post('/staff/staff-accounts', 'Controllers\Staff\StaffController@store', ['auth', 'role:captain', 'csrf']);
$router->post('/staff/staff-accounts/{id}/update', 'Controllers\Staff\StaffController@update', ['auth', 'role:captain', 'csrf']);
$router->post('/staff/staff-accounts/{id}/deactivate', 'Controllers\Staff\StaffController@deactivate', ['auth', 'role:captain', 'csrf']);
$router->get('/staff/settings', 'Controllers\Staff\SettingsController@show', ['auth', 'role:captain']);
$router->post('/staff/settings', 'Controllers\Staff\SettingsController@update', ['auth', 'role:captain', 'csrf']);
$router->get('/staff/reports', 'Controllers\Staff\ReportController@index', ['auth', 'role:captain,secretary']);

// ── Dispatch ──────────────────────────────────────────────
$router->dispatch();
