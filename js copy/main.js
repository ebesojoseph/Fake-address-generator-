/* shared.js — Navigation, Footer, Carousel, Scroll behavior */

(function () {
  /* ── NAV HTML ─────────────────────────────────────────── */
  const NAV_HTML = `
<nav class="navbar" id="mainNav">
  <div class="container" style="display:flex;align-items:center;justify-content:space-between;width:100%;">
    <a href="/index.html" class="nav-logo">
      <img src="/images/logo.svg" alt="Ategha Daniel" onerror="this.style.display='none';this.parentElement.innerHTML='<span style=&quot;font-family:var(--font-display);font-size:1.4rem;color:var(--color-gold);font-style:italic;&quot;>Ategha Daniel</span>'"/>
    </a>
    <ul class="nav-items" id="navItems">
      <li><a href="/index.html">Home</a></li>
      <li><a href="/training.html">Training & Coaching</a></li>
      <li><a href="/blog.html">Blog</a></li>
      <li><a href="/about.html">About Us</a></li>
      <li><a href="/contact.html">Contact</a></li>
    </ul>
    <div class="nav-toggle" id="navToggle" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
  <a href="/index.html">Home</a>
  <a href="/training.html">Training & Coaching</a>
  <a href="/blog.html">Blog</a>
  <a href="/about.html">About Us</a>
  <a href="/contact.html">Contact</a>
</div>`;

  /* ── FOOTER HTML ──────────────────────────────────────── */
  const FOOTER_HTML = `
<footer class="site-footer">
  <div class="container">
    <div class="footer-statement">
      <p>"This website is not here to convince everyone; it is here to protect the right people. If someone leaves this page and decides not to work with us, they do so fully aware of the risks—and that, in real estate, is already a win."</p>
    </div>
    <div class="footer-main-grid">
      <div class="footer-brand">
        <img src="/images/logo.svg" alt="Ategha Daniel" class="footer-logo" onerror="this.style.display='none';this.insertAdjacentHTML('afterend','<span style=\\'font-family:var(--font-display);font-size:1.3rem;color:var(--color-gold);font-style:italic;\\'>Ategha Daniel</span>')"/>
        <div class="footer-socials" style="margin-top:1.25rem;">
          <a href="https://www.youtube.com/@ateghadaniel" target="_blank" aria-label="YouTube">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
          </a>
          <a href="https://www.tiktok.com/@ateghadaniel" target="_blank" aria-label="TikTok">
            <svg width="14" height="14" viewBox="0 0 448 512" fill="currentColor"><path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.32h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/></svg>
          </a>
          <a href="https://www.facebook.com/share/1YyEPur35Z/" target="_blank" aria-label="Facebook">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
          </a>
          <a href="https://www.instagram.com/ategha_daniel/" target="_blank" aria-label="Instagram">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.332 3.608 1.308.975.975 1.246 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.332 2.633-1.308 3.608-.975.975-2.242 1.246-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.332-3.608-1.308-.975-.975-1.246-2.242-1.308-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.332-2.633 1.308-3.608.975-.975 2.242-1.246 3.608-1.308 1.266-.058 1.646-.07 4.85-.07zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948s.014 3.667.072 4.947c.2 4.337 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072s3.667-.014 4.947-.072c4.337-.2 6.78-2.618 6.98-6.98.058-1.281.072-1.689.072-4.948s-.014-3.667-.072-4.947c-.2-4.358-2.618-6.78-6.98-6.98-1.281-.059-1.689-.073-4.948-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
          </a>
          <a href="https://www.linkedin.com/in/ateghadaniel/" target="_blank" aria-label="LinkedIn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z"/></svg>
          </a>
          <a href="https://x.com/AteghaDaniel" target="_blank" aria-label="X / Twitter">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.045 4.126H5.078z"/></svg>
          </a>
        </div>
      </div>
      <nav class="footer-nav">
        <ul>
          <li><a href="/index.html">Home</a></li>
          <li><a href="/training.html">Training</a></li>
          <li><a href="/blog.html">Blog</a></li>
          <li><a href="/about.html">About Us</a></li>
          <li><a href="/contact.html">Contact</a></li>
        </ul>
      </nav>
      <div class="footer-contact">
        <ul>
          <li><strong>Loc:</strong> Buea, Cameroon</li>
          <li><strong>Tel:</strong> +237 679 139 580</li>
          <li><strong>Mail:</strong> Info@ateghadaniel.com</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 Ategha Daniel. All Rights Reserved.</p>
    </div>
  </div>
</footer>`;

  /* ── INJECT ───────────────────────────────────────────── */
  const headerEl = document.getElementById('site-header') || document.querySelector('body');
  if (document.getElementById('site-header')) {
    document.getElementById('site-header').innerHTML = NAV_HTML;
  } else {
    const navWrap = document.createElement('div');
    navWrap.innerHTML = NAV_HTML;
    document.body.insertBefore(navWrap, document.body.firstChild);
  }

  const footerEl = document.getElementById('site-footer');
  if (footerEl) footerEl.innerHTML = FOOTER_HTML;

  /* ── SCROLL ───────────────────────────────────────────── */
  function handleScroll() {
    const nav = document.getElementById('mainNav');
    if (!nav) return;
    if (window.scrollY > 80) nav.classList.add('scrolled');
    else nav.classList.remove('scrolled');
  }

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();

  /* ── ACTIVE NAV LINK ──────────────────────────────────── */
  const path = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-items a, .mobile-menu a').forEach(a => {
    const href = a.getAttribute('href').split('/').pop() || 'index.html';
    if (href === path) a.classList.add('active');
  });

  /* ── MOBILE MENU ──────────────────────────────────────── */
  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('#navToggle');
    const menu = document.getElementById('mobileMenu');
    const navToggle = document.getElementById('navToggle');
    if (!toggle && !menu) return;

    if (toggle) {
      const isOpen = menu.classList.toggle('open');
      navToggle.classList.toggle('open', isOpen);
      document.body.style.overflow = isOpen ? 'hidden' : '';
    } else if (e.target.closest('#mobileMenu') && e.target.tagName === 'A') {
      menu.classList.remove('open');
      navToggle && navToggle.classList.remove('open');
      document.body.style.overflow = '';
    }
  });

  /* ── CAROUSEL ─────────────────────────────────────────── */
  function initCarousel() {
    const slides = document.getElementById('carouselSlides');
    const dotsContainer = document.getElementById('carouselDots');
    if (!slides || !dotsContainer) return;

    const total = slides.children.length;
    let current = 0;
    let timer;

    dotsContainer.innerHTML = '';
    for (let i = 0; i < total; i++) {
      const btn = document.createElement('button');
      btn.className = 'dot-btn' + (i === 0 ? ' active' : '');
      btn.setAttribute('aria-label', 'Go to slide ' + (i + 1));
      btn.addEventListener('click', () => goTo(i));
      dotsContainer.appendChild(btn);
    }

    function goTo(index) {
      current = index;
      slides.style.transform = `translateX(-${current * 100}%)`;
      document.querySelectorAll('.dot-btn').forEach((d, i) => {
        d.classList.toggle('active', i === current);
      });
      resetTimer();
    }

    function resetTimer() {
      clearInterval(timer);
      timer = setInterval(() => goTo((current + 1) % total), 5000);
    }

    resetTimer();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarousel);
  } else {
    initCarousel();
  }

})();