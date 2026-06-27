<!DOCTYPE html>
<html lang="<?= \Core\Session::get('locale', 'en') ?>" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? \Core\View::e($pageTitle) . ' — ' : '' ?><?= BARANGAY_NAME ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: { DEFAULT: '#1d4ed8', hover: '#1e40af', light: '#dbeafe' },
          },
          fontFamily: { sans: ['"Inter"', 'system-ui', 'sans-serif'] }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= \Core\View::asset('css/app.css') ?>">
</head>
<body class="bg-slate-50 font-sans antialiased">

<?php
$user        = \Core\Auth::user();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$residentNav = [
  '/resident/dashboard' => [
    'label' => 'Dashboard',
    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
  ],
  '/resident/requests'  => [
    'label' => 'My Requests',
    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
  ],
  '/resident/profile'   => [
    'label' => 'My Profile',
    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
  ],
];
?>

<div class="flex h-screen overflow-hidden">

  <!-- ── Sidebar ──────────────────────────────────────── -->
  <aside id="sidebar"
         class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col
                transform -translate-x-full transition-transform duration-200 ease-in-out
                lg:relative lg:translate-x-0 lg:flex">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100">
      <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </div>
      <div class="min-w-0">
        <p class="text-sm font-bold text-slate-900 truncate"><?= BARANGAY_NAME ?></p>
        <p class="text-xs text-slate-400">Resident Portal</p>
      </div>
    </div>

    <!-- User Badge -->
    <div class="px-4 py-3 border-b border-slate-100">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
          <span class="text-sm font-semibold text-primary">
            <?= strtoupper(substr($user['first_name'] ?? 'R', 0, 1)) ?>
          </span>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-medium text-slate-900 truncate">
            <?= \Core\View::e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
          </p>
          <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
            Resident
          </span>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <?php foreach ($residentNav as $href => $item):
        $isActive = str_starts_with($currentPath, $href);
      ?>
      <a href="<?= $href ?>"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                <?= $isActive
                    ? 'bg-primary text-white'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <?= $item['icon'] ?>
        </svg>
        <?= $item['label'] ?>
      </a>
      <?php endforeach; ?>

      <!-- Divider -->
      <div class="pt-4 border-t border-slate-100 mt-4">
        <a href="/" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
          </svg>
          Public Site
        </a>
        <form method="POST" action="/logout">
          <input type="hidden" name="_csrf_token" value="<?= \Core\Session::generateCsrfToken() ?>">
          <button type="submit"
                  class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Log Out
          </button>
        </form>
      </div>
    </nav>
  </aside>

  <!-- Sidebar overlay (mobile) -->
  <div id="sidebar-overlay"
       class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"
       onclick="closeSidebar()"></div>

  <!-- ── Main Area ─────────────────────────────────────── -->
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    <!-- Top Bar -->
    <header class="bg-white border-b border-slate-200 h-14 flex items-center px-4 gap-4 flex-shrink-0">
      <button onclick="openSidebar()"
              class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors"
              aria-label="Open menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <div class="flex-1">
        <h1 class="text-base font-semibold text-slate-900">
          <?= isset($pageTitle) ? \Core\View::e($pageTitle) : 'Dashboard' ?>
        </h1>
      </div>

      <a href="/resident/requests/new"
         class="hidden sm:inline-flex items-center gap-2 bg-primary text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Request
      </a>
    </header>

    <!-- Flash Messages -->
    <?php if (\Core\Session::hasFlash('success') || \Core\Session::hasFlash('error')): ?>
    <div class="px-4 pt-4">
      <?php \Core\View::partial('alerts'); ?>
    </div>
    <?php endif; ?>

    <!-- Page Content -->
    <main id="main-content" class="flex-1 overflow-y-auto p-4 sm:p-6">
      <?= $content ?>
    </main>
  </div>
</div>

<script src="<?= \Core\View::asset('js/app.js') ?>"></script>
<script>
  function openSidebar() {
    document.getElementById('sidebar').classList.remove('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
  }
</script>
</body>
</html>
