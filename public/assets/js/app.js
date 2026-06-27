/**
 * Barangay Management System — Global JavaScript
 * Vanilla JS only. No framework dependencies.
 */

'use strict';

/* ── Auto-dismiss flash messages ─────────────────────────── */
(function dismissAlerts() {
  const alerts = document.querySelectorAll('[role="alert"]');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.4s ease';
      alert.style.opacity    = '0';
      setTimeout(() => alert.remove(), 400);
    }, 5000);
  });
})();

/* ── Confirm dialogs for destructive actions ─────────────── */
document.addEventListener('click', function (e) {
  const btn = e.target.closest('[data-confirm]');
  if (!btn) return;
  const message = btn.dataset.confirm || 'Are you sure?';
  if (!window.confirm(message)) {
    e.preventDefault();
    e.stopPropagation();
  }
});

/* ── Form submit loading state ───────────────────────────── */
document.addEventListener('submit', function (e) {
  const form   = e.target;
  const submit = form.querySelector('[type="submit"]');
  if (!submit || submit.dataset.noLoading) return;

  const original = submit.innerHTML;
  submit.disabled   = true;
  submit.innerHTML  = `
    <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    Processing...`;

  // Re-enable after 15s as safety fallback
  setTimeout(() => {
    submit.disabled  = false;
    submit.innerHTML = original;
  }, 15000);
});

/* ── File input: show selected filename ──────────────────── */
document.addEventListener('change', function (e) {
  const input = e.target;
  if (input.type !== 'file') return;

  const label = document.querySelector(`label[for="${input.id}"] .file-name`);
  if (!label) return;

  if (input.files && input.files.length > 0) {
    const name = input.files[0].name;
    const size = (input.files[0].size / 1024).toFixed(1);
    label.textContent = `${name} (${size} KB)`;
  } else {
    label.textContent = 'No file chosen';
  }
});

/* ── Auto-resize textareas ───────────────────────────────── */
document.querySelectorAll('textarea[data-autoresize]').forEach(ta => {
  ta.style.overflow = 'hidden';
  const resize = () => {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
  };
  ta.addEventListener('input', resize);
  resize();
});

/* ── Utility: format PHP-style dates client-side ─────────── */
window.BMS = {
  /**
   * Format an ISO date string into readable local format.
   * @param {string} isoString
   * @returns {string}
   */
  formatDate(isoString) {
    if (!isoString) return '—';
    const d = new Date(isoString);
    return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
  },

  /**
   * Format status string into a display label.
   */
  statusLabel(status) {
    const map = {
      pending:      'Pending',
      under_review: 'Under Review',
      approved:     'Approved',
      rejected:     'Rejected',
      released:     'Released',
    };
    return map[status] || status;
  },

  /**
   * Show a toast notification (temporary banner).
   * @param {string} message
   * @param {'success'|'error'|'info'} type
   */
  toast(message, type = 'info') {
    const colors = {
      success: 'bg-green-600',
      error:   'bg-red-600',
      info:    'bg-blue-600',
    };
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-50 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-lg ${colors[type] || colors.info} transition-opacity duration-300`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 3500);
  },
};
