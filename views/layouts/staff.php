<!DOCTYPE html>
<html lang="<?= \Core\Session::get('locale', 'en') ?>" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? \Core\View::e($pageTitle) . ' — ' : '' ?>Staff — <?= BARANGAY_NAME ?></title>

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
$role        = \Core\Auth::role();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$roleLabel = match ($role) {
  'captain'   => 'Barangay Captain',
  'secretary' => 'Secretary',
  'clerk'     => 'Clerk',
  default     => 'Staff',
};
$roleBadgeColor = match ($role) {
  'captain'   => 'bg-amber-100 text-amber-800',
  'secretary' => 'bg-purple-100 text-purple-800',
  'clerk'     => 'bg-teal-100 text-teal-800',
  default     => 'bg-slate-100 text-slate-700',
};

// Nav groups — visibility gated by role
$navGroups = [
  'Operations' => [
    '/staff/dashboard'  => ['label' => 'Dashboard',   'roles' => ['captain','secretary','clerk'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>'],
    '/staff/requests'   => ['label' => 'Requests',    'roles' => ['captain','secretary','clerk'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
    '/staff/residents'  => ['label' => 'Residents',   'roles' => ['captain','secretary','clerk'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
  ],
  'Content' => [
    '/staff/announcements' => ['label' => 'Announcements', 'roles' => ['captain','secretary'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>'],
    '/staff/projects'      => ['label' => 'Projects',      'roles' => ['captain','secretary'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
    '/staff/events'        => ['label' => 'Events',         'roles' => ['captain','secretary'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
    '/staff/hotlines'      => ['label' => 'Hotlines',       'roles' => ['captain','secretary'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>'],
    '/staff/forms'         => ['label' => 'Public Forms',   'roles' => ['captain','secretary'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
  ],
  'Admin' => [
    '/staff/reports'        => ['label' => 'Reports',        'roles' => ['captain','secretary'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
    '/staff/staff-accounts' => ['label' => 'Staff Accounts', 'roles' => ['captain'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>'],
    '/staff/settings'       => ['label' => 'Settings',       'roles' => ['captain'],
      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
  ],
];
?>

<div class="flex h-screen overflow-hidden">

  <!-- ── Sidebar ──────────────────────────────────────── -->
  <aside id="sidebar"
         class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-100 flex flex-col
                transform -translate-x-full transition-transform duration-200 ease-in-out
                lg:relative lg:translate-x-0">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 h-14 border-b border-slate-700 flex-shrink-0">
      <div class="w-7 h-7 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </div>
      <div class="min-w-0">
        <p class="text-sm font-bold text-white truncate"><?= BARANGAY_NAME ?></p>
        <p class="text-xs text-slate-400">Staff Panel</p>
      </div>
    </div>

    <!-- User Badge -->
    <div class="px-4 py-3 border-b border-slate-700 flex-shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center flex-shrink-0">
          <span class="text-sm font-semibold text-white">
            <?= strtoupper(substr($user['first_name'] ?? 'S', 0, 1)) ?>
          </span>
        </div>
        <div class="min-w-0">
          <p class="text-sm font-medium text-white truncate">
            <?= \Core\View::e(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>
          </p>
          <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium <?= $roleBadgeColor ?>">
            <?= $roleLabel ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4">
      <?php foreach ($navGroups as $groupLabel => $links): ?>
        <?php
        // Check if any link in this group is visible to this role
        $visible = array_filter($links, fn($item) => in_array($role, $item['roles'], true));
        if (empty($visible)) continue;
        ?>
        <div class="mb-5">
          <p class="px-3 mb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider">
            <?= $groupLabel ?>
          </p>
          <?php foreach ($links as $href => $item):
            if (!in_array($role, $item['roles'], true)) continue;
            $isActive = str_starts_with($currentPath, $href);
          ?>
          <a href="<?= $href ?>"
             class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors mb-0.5
                    <?= $isActive
                        ? 'bg-primary text-white'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <?= $item['icon'] ?>
            </svg>
            <?= $item['label'] ?>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>

    <!-- Logout -->
    <div class="border-t border-slate-700 p-3 flex-shrink-0">
      <form method="POST" action="/logout">
        <input type="hidden" name="_csrf_token" value="<?= \Core\Session::generateCsrfToken() ?>">
        <button type="submit"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium
                       text-slate-400 hover:bg-red-900/40 hover:text-red-300 transition-colors">
          <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          Log Out
        </button>
      </form>
    </div>
  </aside>

  <!-- Sidebar overlay (mobile) -->
  <div id="sidebar-overlay"
       class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"
       onclick="closeSidebar()"></div>

  <!-- ── Main Area ─────────────────────────────────────── -->
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

    <!-- Top Bar -->
    <header class="bg-white border-b border-slate-200 h-14 flex items-center px-4 gap-4 flex-shrink-0 z-30">
      <button onclick="openSidebar()"
              class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors"
              aria-label="Open menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <div class="flex-1 min-w-0">
        <h1 class="text-base font-semibold text-slate-900 truncate">
          <?= isset($pageTitle) ? \Core\View::e($pageTitle) : 'Dashboard' ?>
        </h1>
      </div>

      <!-- Quick stats badge -->
      <?php if (isset($pendingCount) && $pendingCount > 0): ?>
      <a href="/staff/requests?status=pending"
         class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-semibold px-2.5 py-1.5 rounded-lg hover:bg-amber-100 transition-colors">
        <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
        <?= $pendingCount ?> pending
      </a>
      <?php endif; ?>

      <span class="text-xs text-slate-400 hidden md:block"><?= date('l, F j, Y') ?></span>
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
