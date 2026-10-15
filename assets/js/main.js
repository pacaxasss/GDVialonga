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

  // Player bio modal (plantel.php)
  const playerModal = document.getElementById('player-modal');
  if (playerModal) {
    const modalImg   = document.getElementById('player-modal-img');
    const modalNum   = document.getElementById('player-modal-number');
    const modalSquad = document.getElementById('player-modal-squad');
    const modalName  = document.getElementById('player-modal-name');
    const modalRole  = document.getElementById('player-modal-role');
    const modalGoals = document.getElementById('player-modal-goals');
    const modalAssist = document.getElementById('player-modal-assists');
    const modalAge   = document.getElementById('player-modal-age');
    const modalNat   = document.getElementById('player-modal-nationality');
    const modalClubs = document.getElementById('player-modal-clubs');
    const modalBio   = document.getElementById('player-modal-bio');
    let lastFocused = null;

    function openPlayerModal(raw) {
      let data;
      try { data = JSON.parse(raw); } catch (e) { return; }
      lastFocused = document.activeElement;

      if (modalImg) {
        if (data.photo) {
          modalImg.src = data.photo;
          modalImg.alt = data.name || '';
          modalImg.hidden = false;
        } else {
          modalImg.removeAttribute('src');
          modalImg.hidden = true;
        }
      }
      if (modalNum) modalNum.textContent = data.number > 0 ? data.number : '';
      modalSquad.textContent = data.squad || '';
      modalName.textContent = data.name || '';
      modalRole.textContent = [data.position, data.nationality].filter(Boolean).join(' · ');
      modalGoals.textContent = data.goals || 0;
      modalAssist.textContent = data.assists || 0;
      modalAge.textContent = data.age ? data.age : '—';
      modalNat.textContent = data.nationality || '—';

      modalClubs.replaceChildren();
      (data.clubs || []).forEach(club => {
        const li = document.createElement('li');
        li.textContent = club;
        modalClubs.appendChild(li);
      });
      modalBio.textContent = data.bio || '';

      playerModal.hidden = false;
      playerModal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      const closeBtn = playerModal.querySelector('.player-modal-close');
      if (closeBtn) closeBtn.focus();
    }

    function closePlayerModal() {
      playerModal.hidden = true;
      playerModal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('modal-open');
      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    }

    document.querySelectorAll('.player-card[data-player]').forEach(card => {
      card.addEventListener('click', () => openPlayerModal(card.dataset.player));
      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPlayerModal(card.dataset.player); }
      });
    });

    playerModal.querySelectorAll('[data-modal-close]').forEach(el => {
      el.addEventListener('click', closePlayerModal);
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !playerModal.hidden) closePlayerModal();
    });
  }

  // Inject keyframes
  if (!document.getElementById('gdv-keyframes')) {
    const s = document.createElement('style');
    s.id = 'gdv-keyframes';
    s.textContent = '@keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }';
    document.head.appendChild(s);
  }

});
