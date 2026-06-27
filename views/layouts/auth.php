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
<body class="bg-slate-50 font-sans antialiased min-h-screen flex items-center justify-center py-12 px-4">

<div class="w-full max-w-md">
  <!-- Logo / Branding -->
  <div class="text-center mb-8">
    <a href="/" class="inline-flex flex-col items-center gap-3">
      <div class="w-14 h-14 bg-primary rounded-2xl flex items-center justify-center shadow-lg">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </div>
      <div>
        <p class="text-xl font-bold text-slate-900"><?= BARANGAY_NAME ?></p>
        <p class="text-sm text-slate-500"><?= BARANGAY_MUNICIPALITY ?>, <?= BARANGAY_PROVINCE ?></p>
      </div>
    </a>
  </div>

  <!-- Flash Messages -->
  <?php \Core\View::partial('alerts'); ?>

  <!-- Card -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
    <?= $content ?>
  </div>

  <!-- Back to home -->
  <p class="text-center mt-6 text-sm text-slate-500">
    <a href="/" class="hover:text-primary transition-colors">&larr; Back to home</a>
  </p>
</div>

<script src="<?= \Core\View::asset('js/app.js') ?>"></script>
</body>
</html>
