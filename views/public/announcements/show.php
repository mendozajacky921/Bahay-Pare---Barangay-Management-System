<?php
$locale = \Core\Session::get('locale', 'en');
$title  = ($locale === 'fil' && !empty($announcement['title_fil'])) ? $announcement['title_fil'] : $announcement['title'];
$body   = ($locale === 'fil' && !empty($announcement['content_fil'])) ? $announcement['content_fil'] : $announcement['content'];
?>
<article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <a href="/announcements" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-primary mb-6">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    <?= $locale === 'fil' ? 'Bumalik sa Mga Anunsyo' : 'Back to Announcements' ?>
  </a>

  <?php if (!empty($announcement['image_url'])): ?>
  <img src="<?= \Core\View::e($announcement['image_url']) ?>"
       alt="<?= \Core\View::e($title) ?>"
       class="w-full h-64 sm:h-80 object-cover rounded-2xl mb-8">
  <?php endif; ?>

  <time class="text-sm text-slate-400" datetime="<?= \Core\View::e($announcement['published_at']) ?>">
    <?= $announcement['published_at'] ? date('F j, Y', strtotime($announcement['published_at'])) : '' ?>
  </time>
  <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 mb-6"><?= \Core\View::e($title) ?></h1>

  <div class="prose">
    <?= nl2br(\Core\View::e($body)) ?>
  </div>
</article>
