/* assets/js/main.js */
(function () {
  'use strict';

  // Field ID map — matches what AddressGenerator::generate() returns
  const FIELD_MAP = {
    'name':           'addr-name',
    'gender':         'addr-gender',
    'street_address': 'addr-street-address',
    'city':           'addr-city',
    'state':          'addr-state',
    'postcode':       'addr-postcode',
    'country_name':   'addr-country-name',
    'phone':          'addr-phone',
    'mobile':         'addr-mobile',
    'email':          'addr-email',
    'username':       'addr-username',
    'company':        'addr-company',
    'job_title':      'addr-job-title',
    'time_zone':      'addr-time-zone',
    'ssn':            'addr-ssn',
    'latitude':       'addr-latitude',
    'longitude':      'addr-longitude',
  };

  const form     = document.getElementById('addr-form');
  const display  = document.getElementById('addr-display');
  const loading  = document.getElementById('addr-loading');
  const genBtn   = document.getElementById('btn-generate');
  const BASE_URL = window.__APP__?.baseUrl || '';

  // ── Generate on button click ───────────────────────────
  if (genBtn) {
    genBtn.addEventListener('click', generateAddress);
  }

  // ── Header quick-generate form ─────────────────────────
  const headerForm = document.getElementById('header-gen-form');
  if (headerForm) {
    headerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const locale = headerForm.querySelector('[name="locale"]').value;
      const gender = headerForm.querySelector('[name="gender"]').value;
      syncForm(locale, gender);
      await generateAddress();
      if (display) display.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  }

  function syncForm(locale, gender) {
    if (!form) return;
    const lf = form.querySelector('[name="locale"]');
    const gf = form.querySelector('[name="gender"]');
    if (lf) lf.value = locale;
    if (gf) gf.value = gender;
  }

  async function generateAddress() {
    if (!form) return;
    const locale = form.querySelector('[name="locale"]')?.value || 'en_US';
    const gender = form.querySelector('[name="gender"]')?.value || 'random';

    if (loading) loading.classList.add('show');
    if (genBtn)  genBtn.disabled = true;

    try {
      const res  = await fetch(`${BASE_URL}/api/generate.php?locale=${encodeURIComponent(locale)}&gender=${encodeURIComponent(gender)}`);
      const json = await res.json();
      if (json.success) {
        renderAddress(json.data);
        animateIn(display?.closest('.card') || display);
      }
    } catch (err) {
      console.error('Generate error:', err);
    } finally {
      if (loading) loading.classList.remove('show');
      if (genBtn)  genBtn.disabled = false;
    }
  }

  function renderAddress(data) {
    if (!display) return;

    // Show/hide rows based on what Faker returned for this locale
    const rows = display.querySelectorAll('.addr-row');
    rows.forEach(row => {
      const val = row.querySelector('.addr-value');
      if (!val) return;
      // Find which field key this row corresponds to
      const id  = val.id;
      const key = Object.keys(FIELD_MAP).find(k => FIELD_MAP[k] === id);
      if (key && data[key] !== undefined && data[key] !== '') {
        val.textContent = data[key];
        row.style.display = '';
      } else if (key) {
        row.style.display = 'none';
      }
    });
  }

  function animateIn(el) {
    if (!el) return;
    el.style.transition = 'opacity .3s, transform .3s';
    el.style.opacity    = '0';
    el.style.transform  = 'translateY(6px)';
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        el.style.opacity   = '1';
        el.style.transform = 'translateY(0)';
      });
    });
  }

  // ── Copy individual field ──────────────────────────────
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.copy-btn');
    if (!btn) return;
    const el = document.getElementById(btn.dataset.target);
    if (!el) return;
    navigator.clipboard.writeText(el.textContent).then(() => {
      const orig = btn.textContent;
      btn.textContent = '✓';
      btn.style.color = '#22c55e';
      setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 1500);
    });
  });

  // ── Copy all fields ────────────────────────────────────
  const copyAllBtn = document.getElementById('copy-all-btn');
  if (copyAllBtn) {
    copyAllBtn.addEventListener('click', () => {
      const rows  = display?.querySelectorAll('.addr-row') || [];
      const lines = [];
      rows.forEach(row => {
        if (row.style.display === 'none') return;
        const label = row.querySelector('.addr-label')?.textContent?.trim() || '';
        const value = row.querySelector('.addr-value')?.textContent?.trim() || '';
        if (value) lines.push(`${label} ${value}`);
      });
      navigator.clipboard.writeText(lines.join('\n')).then(() => {
        copyAllBtn.textContent = '✓ Copied!';
        setTimeout(() => { copyAllBtn.textContent = 'Copy All'; }, 2000);
      });
    });
  }

}());
