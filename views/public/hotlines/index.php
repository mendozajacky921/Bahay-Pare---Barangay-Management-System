<?php $locale = \Core\Session::get('locale', 'en'); ?>
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="mb-10">
    <p class="text-primary font-semibold text-sm uppercase tracking-widest mb-1">
      <?= $locale === 'fil' ? 'Tulong sa Emerhensya' : 'Emergency Services' ?>
    </p>
    <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">
      <?= $locale === 'fil' ? 'Mga Hotline' : 'Emergency Hotlines' ?>
    </h1>
    <p class="mt-3 text-slate-500">
      <?= $locale === 'fil'
          ? 'Para sa mga emerhensya, tumawag kaagad. Huwag mag-atubili.'
          : 'In case of emergency, call immediately. Do not hesitate.' ?>
    </p>
  </div>

  <?php if (empty($groupedHotlines)): ?>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-12 text-center text-slate-500">
      <?= $locale === 'fil' ? 'Walang hotline na nakalista sa ngayon.' : 'No hotlines listed at this time.' ?>
    </div>
  <?php else: ?>
    <div class="space-y-10">
      <?php foreach ($groupedHotlines as $category => $hotlines): ?>
      <div>
        <h2 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-3 pb-2 border-b border-slate-200">
          <?= \Core\View::e($category) ?>
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <?php foreach ($hotlines as $h):
            $name = ($locale === 'fil' && !empty($h['name_fil'])) ? $h['name_fil'] : $h['name'];
          ?>
          <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl px-4 py-4 hover:shadow-sm transition-shadow">
            <div class="min-w-0 mr-4">
              <p class="text-sm font-semibold text-slate-900 truncate"><?= \Core\View::e($name) ?></p>
              <?php if (!empty($h['alt_number'])): ?>
              <p class="text-xs text-slate-400 mt-0.5"><?= \Core\View::e($h['alt_number']) ?></p>
              <?php endif; ?>
            </div>
            <?php
            // M2-MEDIUM-04 fix: normalise Philippine numbers for tel: links.
            // Numbers stored as "0917-xxx-xxxx" need the leading 0 replaced
            // with the +63 country code so mobile dialers connect correctly.
            // Numbers already starting with +63 or 63 are left as-is.
            // Landlines like "(045) 123-4567" keep their leading 0 since area
            // codes should not be stripped — browsers handle landline formatting.
            $rawPhone = preg_replace('/[^0-9+]/', '', $h['phone_number']);
            if (str_starts_with($rawPhone, '09') && strlen($rawPhone) === 11) {
                $telHref = '+63' . substr($rawPhone, 1);
            } elseif (str_starts_with($rawPhone, '63') && !str_starts_with($rawPhone, '+')) {
                $telHref = '+' . $rawPhone;
            } else {
                $telHref = $rawPhone;
            }
            ?>
            <a href="tel:<?= \Core\View::e($telHref) ?>"
               class="flex-shrink-0 inline-flex items-center gap-2 bg-primary text-white text-sm font-semibold
                      px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13
                     a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0
                     01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              <?= \Core\View::e($h['phone_number']) ?>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- General reminder -->
  <div class="mt-12 bg-red-50 border border-red-200 rounded-2xl p-6 flex gap-4">
    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
      <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
    </div>
    <div>
      <p class="text-sm font-semibold text-red-800">
        <?= $locale === 'fil' ? 'Para sa matinding emerhensya' : 'For life-threatening emergencies' ?>
      </p>
      <p class="text-sm text-red-700 mt-1">
        <?= $locale === 'fil'
            ? 'Tumawag kaagad sa 911. Ang 911 ay ang pambansang emergency hotline ng Pilipinas.'
            : 'Call 911 immediately. 911 is the Philippine national emergency hotline.' ?>
      </p>
    </div>
  </div>
</section>
