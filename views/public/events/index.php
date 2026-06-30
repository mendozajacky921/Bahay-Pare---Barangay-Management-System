<?php $locale = \Core\Session::get('locale', 'en'); ?>
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="mb-10">
    <p class="text-primary font-semibold text-sm uppercase tracking-widest mb-1">
      <?= $locale === 'fil' ? 'Mga Aktibidad' : 'Community' ?>
    </p>
    <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">
      <?= $locale === 'fil' ? 'Mga Kaganapan' : 'Events' ?>
    </h1>

    <?php if (!empty($showingPast)): ?>
    <p class="mt-2 text-sm text-slate-400">
      <?= $locale === 'fil'
          ? 'Walang darating na kaganapan. Narito ang mga nakaraang kaganapan.'
          : 'No upcoming events. Showing past events instead.' ?>
    </p>
    <?php endif; ?>
  </div>

  <?php if (empty($events)): ?>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-12 text-center text-slate-500">
      <?= $locale === 'fil' ? 'Walang kaganapan sa ngayon.' : 'No events at this time.' ?>
    </div>
  <?php else: ?>
    <div class="space-y-5">
      <?php foreach ($events as $event):
        $title     = ($locale === 'fil' && !empty($event['title_fil'])) ? $event['title_fil'] : $event['title'];
        $desc      = ($locale === 'fil' && !empty($event['description_fil'])) ? $event['description_fil'] : $event['description'];
        $eventDate = !empty($event['event_date']) ? strtotime($event['event_date']) : null;
        $isPast    = $eventDate && $eventDate < time();
      ?>
      <article class="flex flex-col sm:flex-row bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-md transition-shadow">

        <!-- M2-HIGH-01 fix: static conditional class blocks instead of
             dynamic Tailwind class construction (bg-<?= $var ?> pattern
             breaks when Tailwind is run in build/purge mode). -->
        <?php if ($isPast): ?>
        <div class="flex-shrink-0 flex flex-row sm:flex-col items-center justify-center
                    bg-slate-100 px-5 py-4 sm:w-24 sm:py-6 gap-3 sm:gap-0">
          <?php if ($eventDate): ?>
          <span class="text-xs font-bold uppercase text-slate-400"><?= date('M', $eventDate) ?></span>
          <span class="text-2xl sm:text-3xl font-extrabold text-slate-500 sm:leading-none"><?= date('j', $eventDate) ?></span>
          <span class="text-xs text-slate-400"><?= date('Y', $eventDate) ?></span>
          <?php else: ?>
          <span class="text-slate-400 text-xs">TBA</span>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="flex-shrink-0 flex flex-row sm:flex-col items-center justify-center
                    bg-primary px-5 py-4 sm:w-24 sm:py-6 gap-3 sm:gap-0">
          <?php if ($eventDate): ?>
          <span class="text-xs font-bold uppercase text-blue-200"><?= date('M', $eventDate) ?></span>
          <span class="text-2xl sm:text-3xl font-extrabold text-white sm:leading-none"><?= date('j', $eventDate) ?></span>
          <span class="text-xs text-blue-200"><?= date('Y', $eventDate) ?></span>
          <?php else: ?>
          <span class="text-blue-200 text-xs">TBA</span>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="p-5 min-w-0 flex flex-col">
          <?php if ($isPast): ?>
          <span class="inline-flex self-start items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 mb-2">
            <?= $locale === 'fil' ? 'Nakaraang Kaganapan' : 'Past Event' ?>
          </span>
          <?php endif; ?>
          <h2 class="text-base font-semibold text-slate-900 mb-1">
            <a href="/events/<?= \Core\View::e($event['id']) ?>" class="hover:text-primary transition-colors">
              <?= \Core\View::e($title) ?>
            </a>
          </h2>
          <p class="text-xs text-slate-400 mb-2 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <?= \Core\View::e($event['location']) ?>
            <?php if ($eventDate): ?>
              &nbsp;·&nbsp;<?= date('g:i A', $eventDate) ?>
            <?php endif; ?>
          </p>
          <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed">
            <?= \Core\View::e(strip_tags($desc)) ?>
          </p>
          <a href="/events/<?= \Core\View::e($event['id']) ?>"
             class="inline-flex items-center gap-1 text-primary text-sm font-medium mt-3 hover:underline self-start">
            <?= $locale === 'fil' ? 'Tingnan ang detalye' : 'View details' ?>
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
        'baseUrl'     => '/events',
    ]); ?>
  <?php endif; ?>
</section>
