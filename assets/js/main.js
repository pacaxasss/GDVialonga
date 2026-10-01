document.addEventListener('DOMContentLoaded', function () {

  document.addEventListener('error', function (event) {
    const image = event.target;
    if (!(image instanceof HTMLImageElement)) return;
    if (image.hasAttribute('data-hide-on-error')) image.hidden = true;
    if (image.hasAttribute('data-logo-fallback')) {
      image.hidden = true;
      if (image.nextElementSibling) image.nextElementSibling.hidden = false;
    }
  }, true);

  // Sticky header
  const siteHeader = document.getElementById('site-header');
  if (siteHeader) {
    window.addEventListener('scroll', () => {
      siteHeader.classList.toggle('scrolled', window.scrollY > 40);
    }, { passive: true });
  }

  // Mobile menu
  const toggle = document.getElementById('mobile-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  if (toggle && mobileMenu) {
    toggle.addEventListener('click', () => {
      const isOpen = mobileMenu.classList.toggle('open');
      toggle.classList.toggle('open', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
    });
    document.addEventListener('click', (e) => {
      if (!toggle.contains(e.target) && !mobileMenu.contains(e.target)) {
        mobileMenu.classList.remove('open');
        toggle.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const headerH = siteHeader ? siteHeader.offsetHeight + 16 : 80;
        window.scrollTo({ top: target.offsetTop - headerH, behavior: 'smooth' });
      }
    });
  });

  // Subtle fade-in on scroll
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animation = 'fadeUp .35s ease-out forwards';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.news-card, .player-card, .fixture-card, .board-item, .timeline-item').forEach(el => {
      el.style.opacity = '0';
      observer.observe(el);
    });
  }

  // Form helper
  function showSuccess(form, message) {
    const msg = document.createElement('div');
    msg.className = 'notice-box';
    msg.style.marginTop = '1rem';
    msg.textContent = message;
    form.reset();
    form.appendChild(msg);
    setTimeout(() => msg.remove(), 7000);
  }

  // Inscricao form
  const inscForm = document.getElementById('inscricao-form');
  if (inscForm) {
    inscForm.addEventListener('submit', function (e) {
      e.preventDefault();
      let valid = true;
      this.querySelectorAll('[required]').forEach(input => {
        if (!input.value.trim()) { valid = false; input.style.borderColor = 'var(--gdv-red)'; }
        else { input.style.borderColor = ''; }
      });
      if (valid) showSuccess(inscForm, 'Pedido enviado! A Sra. Rita vai entrar em contacto nas proximas 48h.');
    });
  }

  // Contact form
  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      showSuccess(contactForm, 'Mensagem recebida! Respondemos normalmente no mesmo dia ou no dia seguinte.');
    });
  }

  // Share buttons
  document.querySelectorAll('.share-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const url = encodeURIComponent(window.location.href);
      const title = encodeURIComponent(document.title);
      let shareUrl = '';
      if (this.classList.contains('fb')) shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
      if (this.classList.contains('wa')) shareUrl = `https://api.whatsapp.com/send?text=${title}%20${url}`;
      if (this.classList.contains('tw')) shareUrl = `https://twitter.com/intent/tweet?text=${title}&url=${url}`;
      if (shareUrl) window.open(shareUrl, '_blank', 'noopener,noreferrer,width=600,height=400');
    });
  });

  // Comp tabs (futebol.php)
  document.querySelectorAll('.comp-tab').forEach(tab => {
    tab.addEventListener('click', function () {
      document.querySelectorAll('.comp-tab').forEach(t => { t.classList.remove('active'); });
      this.classList.add('active');
      const target = this.dataset.target;
      document.querySelectorAll('[data-panel]').forEach(p => { p.hidden = p.dataset.panel !== target; });
    });
  });

  // Filter buttons (noticias.php, plantel.php)
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const filter = this.dataset.filter;
      // news filter
      document.querySelectorAll('[data-filterable] .news-card').forEach(card => {
        card.style.display = (filter === 'all' || card.dataset.category === filter) ? '' : 'none';
      });
      // squad filter
      document.querySelectorAll('[data-squad]').forEach(el => {
        el.style.display = (filter === 'all' || el.dataset.squad === filter) ? '' : 'none';
      });
    });
  });

  // Inject keyframes
  if (!document.getElementById('gdv-keyframes')) {
    const s = document.createElement('style');
    s.id = 'gdv-keyframes';
    s.textContent = '@keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }';
    document.head.appendChild(s);
  }

});
