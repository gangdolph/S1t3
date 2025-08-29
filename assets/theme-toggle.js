document.body.classList.add('vap-lines');
const root = document.documentElement;
const modal = document.getElementById('theme-modal');
const openBtn = document.getElementById('theme-toggle');
const closeBtn = document.getElementById('theme-close');
const preview = document.getElementById('theme-preview');
const optionsContainer = modal ? modal.querySelector('.theme-options') : null;
let themes = {};

function applyTheme(name) {
  const t = themes[name];
  if (!t) return;
  root.setAttribute('data-theme', name);
  if (preview) preview.setAttribute('data-theme', name);
  Object.keys(t.vars || {}).forEach(k => root.style.setProperty(k, t.vars[k]));
  if (window.generateVaporwavePattern) {
    if (t.pattern) {
      window.generateVaporwavePattern(t.pattern);
    } else {
      window.generateVaporwavePattern({});
    }
  }
  localStorage.setItem('theme', name);
}

function buildOptions() {
  if (!optionsContainer) return;
  optionsContainer.innerHTML = '';
  Object.keys(themes).forEach(name => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn';
    btn.dataset.theme = name;
    btn.textContent = themes[name].label || name;
    btn.addEventListener('click', () => applyTheme(name));
    btn.addEventListener('focus', () => { if (preview) preview.setAttribute('data-theme', name); });
    btn.addEventListener('mouseenter', () => { if (preview) preview.setAttribute('data-theme', name); });
    optionsContainer.appendChild(btn);
  });
}

async function initThemes() {
  try {
    const res = await fetch('/assets/themes.json');
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (data && typeof data === 'object' && !Array.isArray(data)) {
      themes = data;
    } else {
      throw new Error('Invalid theme JSON');
    }
  } catch (e) {
    console.error('Theme load failed', e);
  }
  buildOptions();
  const stored = localStorage.getItem('theme');
  if (stored && themes[stored]) {
    applyTheme(stored);
  } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && themes['dark']) {
    applyTheme('dark');
  } else {
    const first = Object.keys(themes)[0];
    if (first) applyTheme(first);
  }
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

if (openBtn) openBtn.addEventListener('click', openModal);
if (closeBtn) closeBtn.addEventListener('click', closeModal);
if (modal) {
  modal.addEventListener('click', e => {
    if (e.target === modal) closeModal();
  });
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && modal && !modal.hasAttribute('hidden')) closeModal();
});

initThemes();
