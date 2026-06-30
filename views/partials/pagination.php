<?php
/**
 * Pagination partial.
 * Expects: $currentPage, $totalPages, $baseUrl (e.g. '/announcements')
 * Preserves any extra query params already on the request (e.g. ?category=).
 */
$currentPage = $currentPage ?? 1;
$totalPages  = $totalPages  ?? 1;
$baseUrl     = $baseUrl     ?? '';

if ($totalPages <= 1) {
    return;
}

$extraParams = $_GET;
unset($extraParams['page']);

if (!function_exists('pms_pageUrl')) {
    function pms_pageUrl(string $baseUrl, int $page, array $extraParams): string
    {
        $params = $extraParams;
        if ($page > 1) {
            $params['page'] = $page;
        }
        $query = $params ? ('?' . http_build_query($params)) : '';
        return $baseUrl . $query;
    }
}
?>
<nav class="flex items-center justify-center gap-1 mt-10" aria-label="Pagination">
  <a href="<?= \Core\View::e(pms_pageUrl($baseUrl, max(1, $currentPage - 1), $extraParams)) ?>"
     class="px-3 py-2 rounded-lg text-sm font-medium border transition-colors
            <?= $currentPage <= 1
                ? 'text-slate-300 border-slate-200 cursor-not-allowed pointer-events-none'
                : 'text-slate-600 border-slate-300 hover:bg-slate-100' ?>"
     <?= $currentPage <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
    &larr; Prev
  </a>

  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <?php if ($i === 1 || $i === $totalPages || abs($i - $currentPage) <= 1): ?>
      <a href="<?= \Core\View::e(pms_pageUrl($baseUrl, $i, $extraParams)) ?>"
         class="w-9 h-9 flex items-center justify-center rounded-lg text-sm font-medium transition-colors
                <?= $i === $currentPage
                    ? 'bg-primary text-white'
                    : 'text-slate-600 hover:bg-slate-100' ?>">
        <?= $i ?>
      </a>
    <?php elseif ($i === 2 || $i === $totalPages - 1): ?>
      <span class="px-1 text-slate-400">&hellip;</span>
    <?php endif; ?>
  <?php endfor; ?>

  <a href="<?= \Core\View::e(pms_pageUrl($baseUrl, min($totalPages, $currentPage + 1), $extraParams)) ?>"
     class="px-3 py-2 rounded-lg text-sm font-medium border transition-colors
            <?= $currentPage >= $totalPages
                ? 'text-slate-300 border-slate-200 cursor-not-allowed pointer-events-none'
                : 'text-slate-600 border-slate-300 hover:bg-slate-100' ?>"
     <?= $currentPage >= $totalPages ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
    Next &rarr;
  </a>
</nav>
