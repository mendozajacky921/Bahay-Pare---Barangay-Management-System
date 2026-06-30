<?php $locale = \Core\Session::get('locale', 'en'); ?>
<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
    <div>
      <p class="text-primary font-semibold text-sm uppercase tracking-widest mb-1">Community Development</p>
      <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">
        <?= $locale === 'fil' ? 'Mga Proyekto' : 'Projects' ?>
      </h1>
    </div>

    <!-- Status filter -->
    <div class="flex gap-2">
      <?php
      $statuses = ['' => 'All', 'planned' => 'Planned', 'ongoing' => 'Ongoing', 'completed' => 'Completed'];
      foreach ($statuses as $value => $label):
        $isActive = ($selectedStatus === $value);
        $href = $value === '' ? '/projects' : '/projects?status=' . $value;
      ?>
      <a href="<?= \Core\View::e($href) ?>"
         class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                <?= $isActive ? 'bg-primary text-white border-primary' : 'text-slate-600 border-slate-300 hover:bg-slate-100' ?>">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (empty($projects)): ?>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-12 text-center text-slate-500">
      <?= $locale === 'fil' ? 'Walang proyekto na natagpuan.' : 'No projects found for this filter.' ?>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($projects as $project):
        $title = ($locale === 'fil' && !empty($project['title_fil'])) ? $project['title_fil'] : $project['title'];
        $desc  = ($locale === 'fil' && !empty($project['description_fil'])) ? $project['description_fil'] : $project['description'];
        $statusColor = match ($project['status']) {
          'ongoing'   => 'bg-blue-50 text-blue-700 border-blue-200',
          'completed' => 'bg-green-50 text-green-700 border-green-200',
          default     => 'bg-amber-50 text-amber-700 border-amber-200',
        };
      ?>
      <article class="bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-md transition-shadow flex flex-col">
        <?php if (!empty($project['image_url'])): ?>
        <img src="<?= \Core\View::e($project['image_url']) ?>"
             alt="<?= \Core\View::e($title) ?>"
             class="w-full h-40 object-cover">
        <?php endif; ?>
        <div class="p-5 flex flex-col flex-1">
          <span class="inline-flex self-start items-center px-2 py-0.5 rounded-full text-xs font-semibold border <?= $statusColor ?> mb-3">
            <?= ucfirst($project['status']) ?>
          </span>
          <h2 class="text-base font-semibold text-slate-900 mb-2">
            <a href="/projects/<?= \Core\View::e($project['id']) ?>" class="hover:text-primary transition-colors">
              <?= \Core\View::e($title) ?>
            </a>
          </h2>
          <p class="text-sm text-slate-500 line-clamp-3 leading-relaxed flex-1"><?= \Core\View::e(strip_tags($desc)) ?></p>
          <a href="/projects/<?= \Core\View::e($project['id']) ?>"
             class="inline-flex items-center gap-1 text-primary text-sm font-medium mt-4 hover:underline">
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
        'baseUrl'     => '/projects',
    ]); ?>
  <?php endif; ?>
</section>
