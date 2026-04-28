/* assets/js/main.js — Frontend interactions */

(function () {
  'use strict';

  // ── Address Generator Form ──────────────────────────────
  const form      = document.getElementById('addr-form');
  const display   = document.getElementById('addr-display');
  const loading   = document.getElementById('addr-loading');
  const genBtn    = document.getElementById('btn-generate');

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      await generateAddress();
    });
  }

  // Also wire up the quick-generate bar in the header
  const headerForm = document.getElementById('header-gen-form');
  if (headerForm) {
    headerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const country = headerForm.querySelector('[name="country"]').value;
      const mainCountry = form ? form.querySelector('[name="country"]') : null;
      if (mainCountry) mainCountry.value = country;
      await generateAddress();
      if (display) display.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  }

  async function generateAddress() {
    if (!form || !display) return;
    const data = new FormData(form);
    const params = new URLSearchParams({
      country: data.get('country') || 'us',
      gender:  data.get('gender')  || 'random',
      state:   data.get('state')   || '',
    });

    if (loading) loading.classList.add('show');
    if (genBtn)  { genBtn.disabled = true; }

    try {
      const res  = await fetch('/api/generate.php?' + params.toString());
      const json = await res.json();
      if (json.success) {
        renderAddress(json.data);
        animateIn(display.closest('.card') || display);
      } else {
        console.error('Generate error:', json.error);
      }
    } catch (err) {
      console.error('Fetch error:', err);
    } finally {
      if (loading) loading.classList.remove('show');
      if (genBtn)  { genBtn.disabled = false; }
    }
  }

  function renderAddress(d) {
    const fields = {
      'addr-name':    d.name,
      'addr-gender':  d.gender,
      'addr-street':  d.street,
      'addr-city':    d.city,
      'addr-state':   d.state,
      'addr-zip':     d.zip,
      'addr-country': d.country,
      'addr-phone':   d.phone,
      'addr-email':   d.email,
    };
    for (const [id, val] of Object.entries(fields)) {
      const el = document.getElementById(id);
      if (el) el.textContent = val || '—';
    }
  }

  function animateIn(el) {
    if (!el) return;
    el.style.opacity = '0';
    el.style.transform = 'translateY(6px)';
    el.style.transition = 'opacity .3s, transform .3s';
    requestAnimationFrame(() => {
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    });
  }

  // ── State loader (dynamic, based on country) ───────────────
  const countrySelect = form ? form.querySelector('[name="country"]') : null;
  const stateSelect   = form ? form.querySelector('[name="state"]')   : null;

  if (countrySelect && stateSelect) {
    countrySelect.addEventListener('change', async () => {
      const country = countrySelect.value;
      try {
        const res    = await fetch('/api/states.php?country=' + encodeURIComponent(country));
        const json   = await res.json();
        stateSelect.innerHTML = '<option value="">Any State / Region</option>';
        (json.states || []).forEach(s => {
          const opt = document.createElement('option');
          opt.value       = s;
          opt.textContent = s;
          stateSelect.appendChild(opt);
        });
      } catch (e) { /* silently ignore */ }
    });
  }

  // ── Copy-to-clipboard buttons ──────────────────────────────
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.copy-btn');
    if (!btn) return;
    const targetId = btn.dataset.target;
    const el       = document.getElementById(targetId);
    if (!el) return;
    navigator.clipboard.writeText(el.textContent).then(() => {
      const orig = btn.textContent;
      btn.textContent = 'Copied!';
      btn.style.color = '#27ae60';
      setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 1500);
    });
  });

  // ── Copy entire address ────────────────────────────────────
  const copyAllBtn = document.getElementById('copy-all-btn');
  if (copyAllBtn) {
    copyAllBtn.addEventListener('click', () => {
      const rows = document.querySelectorAll('.address-display .addr-row');
      const lines = [];
      rows.forEach(row => {
        const label = row.querySelector('.addr-label');
        const value = row.querySelector('.addr-value');
        if (label && value) lines.push(label.textContent.trim() + ' ' + value.textContent.trim());
      });
      navigator.clipboard.writeText(lines.join('\n')).then(() => {
        copyAllBtn.textContent = '✓ Copied!';
        setTimeout(() => { copyAllBtn.textContent = 'Copy All'; }, 2000);
      });
    });
  }

})();
