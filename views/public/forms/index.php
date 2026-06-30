<?php $locale = \Core\Session::get('locale', 'en'); ?>
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="mb-10">
    <p class="text-primary font-semibold text-sm uppercase tracking-widest mb-1">Resources</p>
    <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">
      <?= $locale === 'fil' ? 'Mga Form na I-download' : 'Downloadable Forms' ?>
    </h1>
    <p class="mt-3 text-slate-500">
      <?= $locale === 'fil'
          ? 'I-download ang mga opisyal na form ng barangay dito.'
          : 'Download official barangay forms here. Forms are in PDF format.' ?>
    </p>
  </div>

  <?php if (empty($forms)): ?>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-12 text-center text-slate-500">
      <?= $locale === 'fil' ? 'Walang form na available sa ngayon.' : 'No forms are available at this time.' ?>
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($forms as $form):
        $title = ($locale === 'fil' && !empty($form['title_fil'])) ? $form['title_fil'] : $form['title'];
        $desc  = $form['description'] ?? null;
        // M2-LOW-02 fix: format as MB when >= 1 MB, otherwise KB.
        $sizeBytes = (int) ($form['file_size'] ?? 0);
        if ($sizeBytes >= 1048576) {
            $size = round($sizeBytes / 1048576, 1) . ' MB';
        } elseif ($sizeBytes > 0) {
            $size = round($sizeBytes / 1024) . ' KB';
        } else {
            $size = null;
        }
      ?>
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4
                  bg-white border border-slate-200 rounded-xl px-5 py-4 hover:shadow-sm transition-shadow">
        <div class="flex items-start gap-4 min-w-0">
          <!-- PDF icon -->
          <div class="flex-shrink-0 w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586
                   3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900"><?= \Core\View::e($title) ?></p>
            <?php if ($desc): ?>
            <p class="text-xs text-slate-500 mt-0.5"><?= \Core\View::e($desc) ?></p>
            <?php endif; ?>
            <div class="flex items-center gap-3 mt-1">
              <?php if ($size): ?>
              <span class="text-xs text-slate-400">PDF · <?= \Core\View::e($size) ?></span>
              <?php endif; ?>
              <?php if (!empty($form['download_count'])): ?>
              <span class="text-xs text-slate-400">
                <?= number_format((int) $form['download_count']) ?>
                <?= $locale === 'fil' ? ' na na-download' : ' downloads' ?>
              </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <a href="/forms/<?= \Core\View::e($form['id']) ?>/download"
           class="flex-shrink-0 inline-flex items-center gap-2 bg-primary text-white text-sm font-semibold
                  px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          <?= $locale === 'fil' ? 'I-download' : 'Download' ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <p class="mt-6 text-xs text-slate-400 text-center">
      <?= $locale === 'fil'
          ? 'Kailangan ng PDF reader para mabuksan ang mga form. Libre ang Adobe Acrobat Reader.'
          : 'You need a PDF reader to open these forms. Adobe Acrobat Reader is free.' ?>
    </p>
  <?php endif; ?>
</section>
