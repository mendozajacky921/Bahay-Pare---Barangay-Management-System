<?php
$locale = \Core\Session::get('locale', 'en');
$title  = ($locale === 'fil' && !empty($project['title_fil'])) ? $project['title_fil'] : $project['title'];
$desc   = ($locale === 'fil' && !empty($project['description_fil'])) ? $project['description_fil'] : $project['description'];

$statusColor = match ($project['status']) {
    'ongoing'   => 'bg-blue-50 text-blue-700 border-blue-200',
    'completed' => 'bg-green-50 text-green-700 border-green-200',
    default     => 'bg-amber-50 text-amber-700 border-amber-200',
};
?>
<article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <a href="/projects" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-primary mb-6">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    <?= $locale === 'fil' ? 'Bumalik sa Mga Proyekto' : 'Back to Projects' ?>
  </a>

  <?php if (!empty($project['image_url'])): ?>
  <img src="<?= \Core\View::e($project['image_url']) ?>"
       alt="<?= \Core\View::e($title) ?>"
       class="w-full h-64 sm:h-80 object-cover rounded-2xl mb-8">
  <?php endif; ?>

  <div class="flex flex-wrap items-center gap-3 mb-4">
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $statusColor ?>">
      <?= ucfirst($project['status']) ?>
    </span>
    <?php if (!empty($project['budget'])): ?>
    <span class="text-sm text-slate-500">
      <?= $locale === 'fil' ? 'Badyet:' : 'Budget:' ?>
      <span class="font-medium text-slate-700">₱<?= number_format((float) $project['budget'], 2) ?></span>
    </span>
    <?php endif; ?>
  </div>

  <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-2"><?= \Core\View::e($title) ?></h1>

  <?php if (!empty($project['start_date']) || !empty($project['end_date'])): ?>
  <p class="text-sm text-slate-400 mb-6">
    <?php if (!empty($project['start_date'])): ?>
      <?= $locale === 'fil' ? 'Simula:' : 'Start:' ?>
      <span class="text-slate-600"><?= date('F j, Y', strtotime($project['start_date'])) ?></span>
    <?php endif; ?>
    <?php if (!empty($project['end_date'])): ?>
      &nbsp;·&nbsp;
      <?= $locale === 'fil' ? 'Katapusan:' : 'End:' ?>
      <span class="text-slate-600"><?= date('F j, Y', strtotime($project['end_date'])) ?></span>
    <?php endif; ?>
  </p>
  <?php endif; ?>

  <div class="prose">
    <?= nl2br(\Core\View::e($desc)) ?>
  </div>
</article>
