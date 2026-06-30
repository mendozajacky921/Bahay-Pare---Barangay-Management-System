<?php
$locale    = \Core\Session::get('locale', 'en');
$title     = ($locale === 'fil' && !empty($event['title_fil'])) ? $event['title_fil'] : $event['title'];
$desc      = ($locale === 'fil' && !empty($event['description_fil'])) ? $event['description_fil'] : $event['description'];
$eventDate = !empty($event['event_date']) ? strtotime($event['event_date']) : null;
$endDate   = !empty($event['end_date'])   ? strtotime($event['end_date'])   : null;
$isPast    = $eventDate && $eventDate < time();
?>
<article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <a href="/events" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-primary mb-6">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    <?= $locale === 'fil' ? 'Bumalik sa Mga Kaganapan' : 'Back to Events' ?>
  </a>

  <?php if (!empty($event['image_url'])): ?>
  <img src="<?= \Core\View::e($event['image_url']) ?>"
       alt="<?= \Core\View::e($title) ?>"
       class="w-full h-64 sm:h-80 object-cover rounded-2xl mb-8">
  <?php endif; ?>

  <?php if ($isPast): ?>
  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 mb-4">
    Past Event
  </span>
  <?php endif; ?>

  <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-4"><?= \Core\View::e($title) ?></h1>

  <!-- Meta block -->
  <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-8 space-y-2">
    <?php if ($eventDate): ?>
    <div class="flex items-center gap-2 text-sm text-slate-600">
      <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      <span>
        <?= date('F j, Y · g:i A', $eventDate) ?>
        <?php if ($endDate && $endDate !== $eventDate): ?>
          &ndash; <?= date('g:i A', $endDate) ?>
          <?php if (date('Y-m-d', $endDate) !== date('Y-m-d', $eventDate)): ?>
            <?= date('F j, Y', $endDate) ?>
          <?php endif; ?>
        <?php endif; ?>
      </span>
    </div>
    <?php endif; ?>

    <?php if (!empty($event['location'])): ?>
    <div class="flex items-center gap-2 text-sm text-slate-600">
      <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      <span><?= \Core\View::e($event['location']) ?></span>
    </div>
    <?php endif; ?>
  </div>

  <div class="prose">
    <?= nl2br(\Core\View::e($desc)) ?>
  </div>
</article>
