(function() {
  const setTheme = theme => document.documentElement.setAttribute('data-theme', theme);
  const stored = localStorage.getItem('theme');
  if (stored) {
    setTheme(stored);
  } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    setTheme('dark');
  }

  const btn = document.getElementById('theme-toggle');
  if (btn) {
    const themes = ['light', 'dark', 'vaporwave'];
    btn.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-theme') || 'light';
      const next = themes[(themes.indexOf(current) + 1) % themes.length];
      setTheme(next);
      localStorage.setItem('theme', next);
    });
  }
})();
