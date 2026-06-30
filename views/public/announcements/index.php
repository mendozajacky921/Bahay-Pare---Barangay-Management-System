<?php $locale = \Core\Session::get('locale', 'en'); ?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="mb-10">
    <p class="text-primary font-semibold text-sm uppercase tracking-widest mb-1">Stay Informed</p>
    <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">
      <?= $locale === 'fil' ? 'Mga Anunsyo' : 'Announcements' ?>
    </h1>
  </div>

  <?php if (empty($announcements)): ?>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-12 text-center text-slate-500">
      <?= $locale === 'fil' ? 'Walang anunsyo sa ngayon.' : 'No announcements have been published yet.' ?>
    </div>
  <?php else: ?>
    <div class="space-y-6">
      <?php foreach ($announcements as $ann):
        $title = ($locale === 'fil' && !empty($ann['title_fil'])) ? $ann['title_fil'] : $ann['title'];
        $body  = ($locale === 'fil' && !empty($ann['content_fil'])) ? $ann['content_fil'] : $ann['content'];
      ?>
      <article class="flex flex-col sm:flex-row gap-5 bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-md transition-shadow">
        <?php if (!empty($ann['image_url'])): ?>
        <img src="<?= \Core\View::e($ann['image_url']) ?>"
             alt="<?= \Core\View::e($title) ?>"
             class="w-full sm:w-48 h-40 sm:h-32 object-cover rounded-xl flex-shrink-0">
        <?php endif; ?>
        <div class="min-w-0">
          <time class="text-xs text-slate-400" datetime="<?= \Core\View::e($ann['published_at']) ?>">
            <?= $ann['published_at'] ? date('F j, Y', strtotime($ann['published_at'])) : '' ?>
          </time>
          <h2 class="text-lg font-semibold text-slate-900 mt-1 mb-2">
            <a href="/announcements/<?= \Core\View::e($ann['id']) ?>" class="hover:text-primary transition-colors">
              <?= \Core\View::e($title) ?>
            </a>
          </h2>
          <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed">
            <?= \Core\View::e(strip_tags($body)) ?>
          </p>
          <a href="/announcements/<?= \Core\View::e($ann['id']) ?>"
             class="inline-flex items-center gap-1 text-primary text-sm font-medium mt-3 hover:underline">
            <?= $locale === 'fil' ? 'Magbasa pa' : 'Read more' ?>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php \Core\View::partial('pagination', [
        'currentPage' => $currentPage,
        'totalPages'  => $totalPages,
        'baseUrl'     => '/announcements',
    ]); ?>
  <?php endif; ?>
</section>
