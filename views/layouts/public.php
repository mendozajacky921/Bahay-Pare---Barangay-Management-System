<!DOCTYPE html>
<html lang="<?= \Core\Session::get('locale', 'en') ?>" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= BARANGAY_NAME ?> — Official Barangay Management System">
  <title><?= isset($pageTitle) ? \Core\View::e($pageTitle) . ' — ' : '' ?><?= BARANGAY_NAME ?></title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary:   { DEFAULT: '#1d4ed8', hover: '#1e40af', light: '#dbeafe' },
            secondary: { DEFAULT: '#0f172a' },
            accent:    { DEFAULT: '#f59e0b' },
          },
          fontFamily: {
            sans: ['"Inter"', 'system-ui', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- App CSS -->
  <link rel="stylesheet" href="<?= \Core\View::asset('css/app.css') ?>">
</head>
<body class="bg-white text-slate-800 font-sans antialiased flex flex-col min-h-screen">

<!-- Skip to main content (accessibility) -->
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-primary focus:text-white focus:px-4 focus:py-2 focus:top-0 focus:left-0">
  Skip to main content
</a>

<!-- ── Navigation ──────────────────────────────────────── -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">

      <!-- Logo -->
      <a href="/" class="flex items-center gap-3 group">
        <div class="w-9 h-9 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
        </div>
        <div class="hidden sm:block">
          <p class="text-sm font-700 text-slate-900 leading-none"><?= BARANGAY_NAME ?></p>
          <p class="text-xs text-slate-500 leading-none mt-0.5"><?= BARANGAY_MUNICIPALITY ?></p>
        </div>
      </a>

      <!-- Desktop Nav Links -->
      <div class="hidden md:flex items-center gap-1">
        <?php
        $navLinks = [
          '/'              => ['label' => 'Home',          'label_fil' => 'Tahanan'],
          '/announcements' => ['label' => 'Announcements', 'label_fil' => 'Anunsyo'],
          '/projects'      => ['label' => 'Projects',      'label_fil' => 'Proyekto'],
          '/events'        => ['label' => 'Events',        'label_fil' => 'Kaganapan'],
          '/hotlines'      => ['label' => 'Hotlines',      'label_fil' => 'Hotline'],
          '/forms'         => ['label' => 'Forms',         'label_fil' => 'Mga Form'],
        ];
        $locale      = \Core\Session::get('locale', 'en');
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        foreach ($navLinks as $href => $info):
          $isActive = ($href === '/' ? $currentPath === '/' : str_starts_with($currentPath, $href));
          $label    = ($locale === 'fil') ? $info['label_fil'] : $info['label'];
        ?>
        <a href="<?= $href ?>"
           class="px-3 py-2 rounded-md text-sm font-medium transition-colors
                  <?= $isActive
                      ? 'bg-primary text-white'
                      : 'text-slate-600 hover:text-primary hover:bg-primary-light' ?>">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Right Side Actions -->
      <div class="flex items-center gap-2">
        <!-- Language Toggle -->
        <form method="POST" action="/set-locale">
          <input type="hidden" name="_csrf_token" value="<?= \Core\Session::generateCsrfToken() ?>">
          <input type="hidden" name="locale" value="<?= $locale === 'en' ? 'fil' : 'en' ?>">
          <button type="submit"
                  class="text-xs font-medium text-slate-500 hover:text-primary border border-slate-300 rounded-md px-2 py-1 transition-colors"
                  title="Switch language">
            <?= $locale === 'en' ? 'FIL' : 'EN' ?>
          </button>
        </form>

        <?php if (\Core\Auth::check()): ?>
          <?php $user = \Core\Auth::user(); ?>
          <a href="<?= \Core\Auth::isStaff() ? '/staff/dashboard' : '/resident/dashboard' ?>"
             class="text-sm font-medium text-primary hover:text-primary-hover">
            Dashboard
          </a>
        <?php else: ?>
          <a href="/login"
             class="text-sm font-medium text-slate-600 hover:text-primary px-3 py-2 rounded-md transition-colors">
            Log In
          </a>
          <a href="/register"
             class="text-sm font-semibold bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Register
          </a>
        <?php endif; ?>

        <!-- Mobile Menu Button -->
        <button id="mobile-menu-btn"
                class="md:hidden p-2 rounded-md text-slate-600 hover:text-primary hover:bg-slate-100"
                aria-label="Open menu" aria-expanded="false" aria-controls="mobile-menu">
          <svg id="menu-icon-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg id="menu-icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden hidden pb-3 border-t border-slate-100 mt-2 pt-2">
      <?php foreach ($navLinks as $href => $info):
        $isActive = ($href === '/' ? $currentPath === '/' : str_starts_with($currentPath, $href));
        $label    = ($locale === 'fil') ? $info['label_fil'] : $info['label'];
      ?>
      <a href="<?= $href ?>"
         class="block px-3 py-2.5 rounded-md text-sm font-medium transition-colors
                <?= $isActive ? 'bg-primary text-white' : 'text-slate-600 hover:bg-slate-100' ?>">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>
  </nav>
</header>

<!-- ── Flash Messages ───────────────────────────────────── -->
<?php if (\Core\Session::hasFlash('success') || \Core\Session::hasFlash('error')): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
  <?php \Core\View::partial('alerts'); ?>
</div>
<?php endif; ?>

<!-- ── Main Content ─────────────────────────────────────── -->
<main id="main-content" class="flex-1">
  <?= $content ?>
</main>

<!-- ── Footer ───────────────────────────────────────────── -->
<footer class="bg-slate-900 text-slate-300 mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div>
        <div class="flex items-center gap-3 mb-4">
          <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </div>
          <div>
            <p class="text-white font-semibold text-sm"><?= BARANGAY_NAME ?></p>
            <p class="text-slate-400 text-xs"><?= BARANGAY_MUNICIPALITY ?>, <?= BARANGAY_PROVINCE ?></p>
          </div>
        </div>
        <p class="text-sm text-slate-400 leading-relaxed">
          Serving our community with transparency and efficiency.
        </p>
      </div>
      <div>
        <h3 class="text-white font-semibold text-sm mb-4">Quick Links</h3>
        <ul class="space-y-2 text-sm">
          <li><a href="/announcements" class="hover:text-white transition-colors">Announcements</a></li>
          <li><a href="/projects" class="hover:text-white transition-colors">Projects</a></li>
          <li><a href="/events" class="hover:text-white transition-colors">Events</a></li>
          <li><a href="/hotlines" class="hover:text-white transition-colors">Emergency Hotlines</a></li>
          <li><a href="/forms" class="hover:text-white transition-colors">Download Forms</a></li>
        </ul>
      </div>
      <div>
        <h3 class="text-white font-semibold text-sm mb-4">Resident Services</h3>
        <ul class="space-y-2 text-sm">
          <li><a href="/register" class="hover:text-white transition-colors">Register an Account</a></li>
          <li><a href="/login" class="hover:text-white transition-colors">Log In</a></li>
          <li><a href="/privacy-policy" class="hover:text-white transition-colors">Privacy Policy</a></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-slate-700 mt-8 pt-6 text-center text-xs text-slate-500">
      &copy; <?= date('Y') ?> <?= BARANGAY_NAME ?>. All rights reserved. — Powered by Barangay MS
    </div>
  </div>
</footer>

<!-- ── Scripts ───────────────────────────────────────────── -->
<script src="<?= \Core\View::asset('js/app.js') ?>"></script>
<script>
  // Mobile menu toggle
  const btn      = document.getElementById('mobile-menu-btn');
  const menu     = document.getElementById('mobile-menu');
  const iconOpen  = document.getElementById('menu-icon-open');
  const iconClose = document.getElementById('menu-icon-close');
  btn.addEventListener('click', () => {
    const isOpen = !menu.classList.contains('hidden');
    menu.classList.toggle('hidden', isOpen);
    iconOpen.classList.toggle('hidden', !isOpen);
    iconClose.classList.toggle('hidden', isOpen);
    btn.setAttribute('aria-expanded', String(!isOpen));
  });
</script>
</body>
</html>
