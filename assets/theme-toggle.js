(function() {
  document.body.classList.add('vap-lines');
  var root = document.documentElement;
  var modal = document.getElementById('theme-modal');
  var openBtn = document.getElementById('theme-toggle');
  var closeBtn = document.getElementById('theme-close');
  var preview = document.getElementById('theme-preview');
  var options = modal ? modal.querySelectorAll('[data-theme]') : [];

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    if (preview) {
      preview.setAttribute('data-theme', theme);
    }
    localStorage.setItem('theme', theme);
  }

  function openModal() {
    if (!modal) return;
    modal.classList.add('open');
    modal.removeAttribute('hidden');
    if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
    modal.focus();
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('hidden', '');
    if (openBtn) {
      openBtn.setAttribute('aria-expanded', 'false');
      openBtn.focus();
    }
  }

  var stored = localStorage.getItem('theme');
  if (stored) {
    applyTheme(stored);
  } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    applyTheme('dark');
  }

  if (openBtn) {
    openBtn.addEventListener('click', openModal);
  }
  if (closeBtn) {
    closeBtn.addEventListener('click', closeModal);
  }
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });
  }
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal && !modal.hasAttribute('hidden')) {
      closeModal();
    }
  });

  Array.prototype.forEach.call(options, function(btn) {
    btn.addEventListener('click', function() {
      applyTheme(btn.getAttribute('data-theme'));
    });
    btn.addEventListener('focus', function() {
      if (preview) preview.setAttribute('data-theme', btn.getAttribute('data-theme'));
    });
    btn.addEventListener('mouseenter', function() {
      if (preview) preview.setAttribute('data-theme', btn.getAttribute('data-theme'));
    });
  });
})();
