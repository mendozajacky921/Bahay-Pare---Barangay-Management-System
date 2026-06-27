<?php
$locale = \Core\Session::get('locale', 'en');

// Hero content by locale
$hero = [
  'en'  => ['headline' => 'Welcome to ' . BARANGAY_NAME,
             'sub'      => 'Request documents, track your applications, and stay informed — all in one place.',
             'cta1'     => 'Request a Document', 'cta2' => 'View Announcements'],
  'fil' => ['headline' => 'Maligayang Pagdating sa ' . BARANGAY_NAME,
             'sub'      => 'Mag-request ng dokumento, subaybayan ang iyong aplikasyon, at manatiling updated.',
             'cta1'     => 'Mag-request ng Dokumento', 'cta2' => 'Tingnan ang Anunsyo'],
];
$h = $hero[$locale] ?? $hero['en'];
?>

<!-- ── Hero ─────────────────────────────────────────────── -->
<section class="relative bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white overflow-hidden">
  <!-- Decorative circles -->
  <div class="absolute -top-32 -right-32 w-96 h-96 bg-blue-600/10 rounded-full pointer-events-none"></div>
  <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-blue-500/10 rounded-full pointer-events-none"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
    <div class="max-w-3xl">
      <div class="inline-flex items-center gap-2 bg-blue-900/60 border border-blue-700 rounded-full px-3 py-1 text-xs font-medium text-blue-300 mb-6">
        <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
        Official Barangay Portal
      </div>
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6 tracking-tight">
        <?= \Core\View::e($h['headline']) ?>
      </h1>
      <p class="text-lg sm:text-xl text-slate-300 mb-8 leading-relaxed max-w-2xl">
        <?= \Core\View::e($h['sub']) ?>
      </p>
      <div class="flex flex-wrap gap-4">
        <?php if (\Core\Auth::check() && \Core\Auth::isResident()): ?>
        <a href="/resident/requests/new"
           class="inline-flex items-center gap-2 bg-white text-slate-900 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors text-sm shadow-lg">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          <?= \Core\View::e($h['cta1']) ?>
        </a>
        <?php else: ?>
        <a href="/register"
           class="inline-flex items-center gap-2 bg-white text-slate-900 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors text-sm shadow-lg">
          <?= \Core\View::e($h['cta1']) ?>
        </a>
        <?php endif; ?>
        <a href="/announcements"
           class="inline-flex items-center gap-2 border border-slate-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/10 transition-colors text-sm">
          <?= \Core\View::e($h['cta2']) ?>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ── Services Grid ─────────────────────────────────────── -->
<section class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14">
      <p class="text-primary font-semibold text-sm uppercase tracking-widest mb-2">Online Services</p>
      <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">
        <?= $locale === 'fil' ? 'Mga Serbisyo' : 'Documents You Can Request' ?>
      </h2>
      <p class="mt-3 text-slate-500 max-w-xl mx-auto">
        <?= $locale === 'fil'
            ? 'Mag-login at i-request ang inyong mga dokumento online.'
            : 'Create an account, submit your request, and track its progress online.' ?>
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $services = [
        ['icon' => '📄', 'title' => 'Barangay Clearance',        'title_fil' => 'Barangay Clearance',
         'desc' => 'Required for employment, business permits, and legal transactions.',
         'desc_fil' => 'Kailangan para sa trabaho, negosyo, at legal na transaksyon.'],
        ['icon' => '🏠', 'title' => 'Certificate of Residency',  'title_fil' => 'Patunay ng Tirahan',
         'desc' => 'Proof that you reside within the barangay jurisdiction.',
         'desc_fil' => 'Patunay na nakatira kayo sa loob ng barangay.'],
        ['icon' => '🤝', 'title' => 'Certificate of Indigency',  'title_fil' => 'Patunay ng Kahirapan',
         'desc' => 'For government assistance programs and hospital fee waivers.',
         'desc_fil' => 'Para sa tulong ng pamahalaan at libre na pagamutan.'],
        ['icon' => '🪪', 'title' => 'Cedula (CTC)',               'title_fil' => 'Cedula (CTC)',
         'desc' => 'Community Tax Certificate required for official transactions.',
         'desc_fil' => 'Kinakailangan sa opisyal na transaksyon.'],
        ['icon' => '🆔', 'title' => 'Barangay ID',               'title_fil' => 'Barangay ID',
         'desc' => 'Official barangay identification card for residents.',
         'desc_fil' => 'Opisyal na ID card para sa mga residente.'],
        ['icon' => '📋', 'title' => 'Track Your Request',        'title_fil' => 'Subaybayan ang Kahilingan',
         'desc' => 'Log in to check the real-time status of your request.',
         'desc_fil' => 'Mag-login para tingnan ang katayuan ng inyong kahilingan.'],
      ];
      foreach ($services as $i => $s):
        $title = ($locale === 'fil') ? $s['title_fil'] : $s['title'];
        $desc  = ($locale === 'fil') ? $s['desc_fil']  : $s['desc'];
      ?>
      <div class="group bg-white border border-slate-200 rounded-2xl p-6 hover:shadow-md hover:border-primary transition-all duration-200">
        <div class="text-3xl mb-4"><?= $s['icon'] ?></div>
        <h3 class="text-base font-semibold text-slate-900 mb-2"><?= \Core\View::e($title) ?></h3>
        <p class="text-sm text-slate-500 leading-relaxed"><?= \Core\View::e($desc) ?></p>
        <?php if ($i < 5): ?>
        <a href="<?= \Core\Auth::check() ? '/resident/requests/new' : '/register' ?>"
           class="inline-flex items-center gap-1 text-primary text-sm font-medium mt-4 hover:underline">
          <?= $locale === 'fil' ? 'Mag-request' : 'Request now' ?>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
        <?php else: ?>
        <a href="<?= \Core\Auth::check() ? '/resident/requests' : '/login' ?>"
           class="inline-flex items-center gap-1 text-primary text-sm font-medium mt-4 hover:underline">
          <?= $locale === 'fil' ? 'Tingnan' : 'View status' ?>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Latest Announcements ──────────────────────────────── -->
<?php if (!empty($announcements)): ?>
<section class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-10">
      <div>
        <p class="text-primary font-semibold text-sm uppercase tracking-widest mb-1">Stay Informed</p>
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">
          <?= $locale === 'fil' ? 'Pinakabagong Anunsyo' : 'Latest Announcements' ?>
        </h2>
      </div>
      <a href="/announcements" class="text-sm font-medium text-primary hover:underline hidden sm:block">
        <?= $locale === 'fil' ? 'Tingnan lahat' : 'View all' ?> &rarr;
      </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach (array_slice($announcements, 0, 3) as $ann):
        $title = ($locale === 'fil' && $ann['title_fil']) ? $ann['title_fil'] : $ann['title'];
        $body  = ($locale === 'fil' && $ann['content_fil']) ? $ann['content_fil'] : $ann['content'];
      ?>
      <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-md transition-shadow">
        <?php if ($ann['image_url']): ?>
        <img src="<?= \Core\View::e($ann['image_url']) ?>"
             alt="<?= \Core\View::e($title) ?>"
             class="w-full h-40 object-cover">
        <?php endif; ?>
        <div class="p-5">
          <time class="text-xs text-slate-400" datetime="<?= $ann['published_at'] ?>">
            <?= date('F j, Y', strtotime($ann['published_at'])) ?>
          </time>
          <h3 class="text-base font-semibold text-slate-900 mt-1 mb-2 line-clamp-2">
            <a href="/announcements/<?= $ann['id'] ?>" class="hover:text-primary transition-colors">
              <?= \Core\View::e($title) ?>
            </a>
          </h3>
          <p class="text-sm text-slate-500 line-clamp-3 leading-relaxed">
            <?= \Core\View::e(strip_tags($body)) ?>
          </p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── How It Works ──────────────────────────────────────── -->
<section class="py-20 bg-white">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <p class="text-primary font-semibold text-sm uppercase tracking-widest mb-2">Simple Process</p>
    <h2 class="text-3xl font-bold text-slate-900 mb-12">
      <?= $locale === 'fil' ? 'Paano Gumamit' : 'How It Works' ?>
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-10">
      <?php
      $steps = $locale === 'fil'
        ? [['1', 'Mag-register', 'Gumawa ng account gamit ang iyong email at valid ID.'],
           ['2', 'Mag-request', 'Piliin ang dokumento na kailangan at punan ang form.'],
           ['3', 'I-claim', 'Hintayin ang email notification at kunin ang dokumento.']]
        : [['1', 'Create an Account', 'Register with your email address and a valid ID for verification.'],
           ['2', 'Submit a Request', 'Choose the document you need and fill in the required details.'],
           ['3', 'Claim Your Document', 'Wait for an email update, then pick up your signed document.']];
      foreach ($steps as $step):
      ?>
      <div class="flex flex-col items-center">
        <div class="w-12 h-12 rounded-full bg-primary text-white text-lg font-bold flex items-center justify-center mb-4 shadow-md">
          <?= $step[0] ?>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-2"><?= $step[1] ?></h3>
        <p class="text-sm text-slate-500 leading-relaxed"><?= $step[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-10">
      <a href="/register"
         class="inline-flex items-center gap-2 bg-primary text-white font-bold px-8 py-3 rounded-xl hover:bg-blue-700 transition-colors text-sm shadow">
        <?= $locale === 'fil' ? 'Magsimula Ngayon' : 'Get Started' ?>
      </a>
    </div>
  </div>
</section>
