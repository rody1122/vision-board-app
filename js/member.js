const toggle = document.getElementById('themeToggle');
const label = document.getElementById('theme-label');
const html = document.documentElement;

// Remember preference
const savedTheme = localStorage.getItem('m3-theme') || 'light';
html.setAttribute('data-theme', savedTheme);

label.textContent = savedTheme === 'dark' ? 'dark' : 'light';

toggle.addEventListener('click', () => {
    const current = html.getAttribute('data-theme');
    const next = current === 'light' ? 'dark' : 'light';
    html.setAttribute('data-theme', next);

    localStorage.setItem('m3-theme', next);
    label.textContent = next === 'dark' ? 'dark' : 'light';
});