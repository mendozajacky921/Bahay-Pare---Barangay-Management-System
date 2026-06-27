<?php
$success = \Core\Session::getFlash('success');
$error   = \Core\Session::getFlash('error');
$errors  = \Core\Session::getFlash('errors', []);
?>

<?php if ($success): ?>
<div role="alert"
     class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm">
  <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
  </svg>
  <span><?= \Core\View::e($success) ?></span>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div role="alert"
     class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm">
  <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
  </svg>
  <span><?= \Core\View::e($error) ?></span>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div role="alert"
     class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 mb-4 text-sm">
  <div class="flex items-center gap-2 font-semibold mb-2">
    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Please fix the following:
  </div>
  <ul class="list-disc list-inside space-y-1">
    <?php foreach ($errors as $field => $message): ?>
    <li><?= \Core\View::e($message) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>
